# Simple E-commerce Shopping Cart (Laravel + React)

A simple e-commerce shopping cart application built with **Laravel, React (Breeze)**, and **Tailwind CSS**.

The project demonstrates clean architecture, service-driven business logic, background jobs, and automated tests.

Users can browse products, manage a shopping cart, and place orders. Background jobs handle low-stock notifications and daily sales reports.

## Table of Contents

- [Tech Stack](#tech-stack)
- [Features](#features)
- [Setup Instructions](#setup-instructions)
- [Cart Management Implementation](#cart-management-implementation)
- [Daily Sales Report](#daily-sales-report)
- [Tests](#testing)

## Tech Stack

- **Backend**: Laravel
- **Frontend**: React (Laravel Breeze)
- **Styling**: Tailwind CSS
- **Auth**: Laravel Breeze
- **Queue**: Laravel Queue (database driver)
- **Scheduler**: Laravel Task Scheduling
- **Mail**: Laravel Mail (log driver for local)
- **Database**: SQLite (local)
- **Version Control**: Git / GitHub

## Features
- User authentication
- Product listing with pagination
- Guest shopping cart (session-based, pre-authentication only)
- User-specific shopping cart (persistent)
- Guest → user cart merge on login
- Add, update, and remove cart items
- Checkout flow with stock validation
- Low stock email notifications (queued)
- Daily sales report email (scheduled job)

## Setup Instructions

**Prerequisites**

- PHP 8.2+
- Composer
- Node.js 18+
- Git

1. Clone the repository
    ```bash
    git clone [https://github.com/ifeekz/trustfactory-cart.git](https://github.com/ifeekz/trustfactory-cart.git)
    cd trustfactory-cart
    ```

2. Install backend dependencies
    ```bash 
    composer install
    ```

3. Install frontend dependencies
    ```bash
    npm install
    ```

4. Environment setup
    ```bash 
    cp .env.example .env
    php artisan key:generate
    ```

    Configure .env:

    ```env
    DB_CONNECTION=sqlite
    QUEUE_CONNECTION=database
    MAIL_MAILER=log

    SHOP_ADMIN_EMAIL=admin@trustfactory.test
    LOW_STOCK_THRESHOLD=5
    ```

    Create database file:

    ```bash
    touch database/database.sqlite
    ```

5. Run migrations & seed data

    ```bash
    php artisan migrate --seed
    ```

    Seeded data includes:

    - Demo user
    - Admin user (used for email notifications)
    - Sample products

6. Build assets
    ```bash
    npm run build
    ```

7. Start the application

    Terminal 1:
    ```bash
    php artisan serve
    ```

    Terminal 2:
    ```bash
    npm run dev
    ```

    Terminal 3 (queue worker):
    ```bash
    php artisan queue:work
    ```

    Terminal 4 (scheduler – local):
    ```bash
    php artisan schedule:work
    ```

8. Access the application at `http://localhost:8080` or setup a virtual host for Apache or Nginx (preferably) to serve the app on url like `http://trustfactory.test`.

See [vhost.md](./vhost.md) for more details on setting up a virtual host.

## Cart Management Implementation

All cart-related business logic lives in a dedicated service class:

```swift
app/Services/CartService.php
```

**Responsibilities**

- Create or retrieve the authenticated user’s cart
- Add products to cart
- Update product quantities
- Remove products from cart
- Validate stock availability
- Handle checkout
- Dispatch low-stock notifications
- Persist orders and order items
- Clear cart after checkout

**Why a Service Layer?**
- Keeps controllers thin and readable
- Centralizes business logic
- Improves testability
- Avoids duplication
- Makes future changes safer

### Cart Persistence Strategy

For authenticated users, all cart operations are persisted in the database and scoped to the user.  
This ensures cart state is durable, consistent, and independent of sessions or frontend storage.

Guest users may build a temporary cart stored in the server-side session. Upon login, this
session cart is merged into the authenticated user’s cart and then cleared. All subsequent cart operations occur against the database-backed cart.

### Guest → User Cart Flow

Guests can freely build a cart using a **session-based cart**.
Checkout is **restricted to authenticated users**.

When a guest logs in:

1. The login process regenerates the session for security.
2. A login event listener merges the guest’s session cart into the user’s persistent cart.
3. Session cart data is cleared.
4. The user proceeds with checkout using the database-backed cart.

This ensures:

- No cart data is lost during login
- Session fixation protection remains intact
- All checkout logic operates on a single, consistent cart source

The merge logic is implemented in an authentication listener:

```php
app/Listeners/MergeGuestCartOnLogin.php
```

**Key Methods**

```php
getCart(User $user): Cart
addProduct(User $user, Product $product, int $quantity): void
updateProductQuantity(User $user, Product $product, int $quantity): void
removeProduct(User $user, Product $product): void
checkout(User $user): Order
```

### Checkout Flow

- Requires an authenticated user
- Runs inside a database transaction
- Verifies stock for each item
- Creates an order and order items
- Decrements product stock
- Dispatches low-stock notifications if applicable
- Clears the user’s cart

This approach ensures **data consistency** and prevents partial checkouts.

### Low Stock Notification

- Triggered when a product’s remaining stock drops below a configurable threshold
- Implemented as a queued job:
    ```php
    app/Jobs/LowStockNotificationJob.php
    ```
- Sends an email to the configured admin address

## Daily Sales Report

The application includes a scheduled background task that sends a daily sales report to the admin email address.

**Overview**

- Runs automatically every evening
- Aggregates all products sold during the day
- Sends a summary email to the configured admin user
- Implemented using Laravel’s Task Scheduling system

**Implementation**

```php
app/Console/Commands/SendDailySalesReport.php
```

The command:

- Queries all OrderItem records created on the current day
- Groups results by product
- Calculates total quantity sold per product
- Sends a summary email only if sales occurred

Command signature:

```bash
php artisan app:send-daily-sales-report
```

**Scheduler**

The command is registered in Laravel’s scheduler and runs daily at **18:00**:

```php
$schedule->command(SendDailySalesReport::class)
    ->dailyAt('18:00');
```


In production, Laravel’s scheduler should be triggered via cron:

```bash
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

For local development, the scheduler can be run manually:

```bash
php artisan schedule:work
```

Emails are sent to the admin address configured via environment variables:

```env
SHOP_ADMIN_EMAIL=admin@trustfactory.test
```

For local development, emails are logged to:

```bash
storage/logs/laravel.log
```

## Testing

The application includes both **unit tests** and **feature tests** to validate business logic and end-to-end cart behavior.

### Test Structure

```bash
tests/
├── Unit/
│   └── Services/
│       └── CartServiceTest.php
│
├── Feature/
│   └── Cart/
│       ├── CartFlowTest.php
│       └── GuestCartFlowTest.php
│   └── Reports/
│       └── DailySalesReportTest.php
```

### Unit Tests

**CartService**

- Adding products to cart
- Updating quantities
- Removing products
- Checkout behavior and stock validation

### Feature Tests

**Cart Flow**

- Viewing cart
- Adding and removing items
- Updating quantities

**Guest → Authenticated Cart Merge**

- Verifies that a guest cart stored in the session is merged into the authenticated user’s cart upon login
- Ensures session cart data is cleared after the merge
- Confirms the merged cart persists correctly for checkout

> Checkout concurrency is covered by a feature test ensuring only one user can purchase the last available item.

**Daily Sales Report**

- Orders and order items created during the day are included
- The report command sends an email when sales exist

These tests use:

- `RefreshDatabase` for isolation
- Queue fakes for job assertions
- Direct service invocation for fast, focused feedback

The tests coverage ensure that critical cart behavior works correctly across both **guest and authenticated** user flows and protect against regressions in session handling, authentication, and checkout logic.

### Running Tests

Run all tests:

```php
php artisan test
```

Run only CartService unit tests:

```php
php artisan test tests/Unit/Services
```

Run only cart flow feature tests:

```php
php artisan test tests/Feature/Services
```

Testing Philosophy

- Business logic lives in services, not controllers
- Unit tests validate domain rules
- Feature tests validate real user flows
<!-- - No UI or frontend coupling in tests -->
- Tests are deterministic, isolated, and fast

This layered approach ensures confidence in both core logic and end-to-end behavior while keeping the codebase maintainable.

**Notes**

- Prices are stored in cents to avoid floating-point issues
- Each user has exactly one cart
- All cart data is persisted per authenticated user (no session or local storage)

## Author

Built by [Nnorom Ifeanyi Paul](https://github.com/ifeekz) as part of the Trustfactory Laravel Developer assessment.
