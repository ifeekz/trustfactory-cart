# Simple E-commerce Shopping Cart (Laravel + React)

A simple e-commerce shopping cart application built with **Laravel, React (Breeze)**, and **Tailwind CSS**.

Users can browse products, manage a shopping cart, and place orders. Background jobs handle low-stock notifications and daily sales reports.

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
- Product listing
- User-specific shopping cart
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

8. Access the application at http://localhost:8000

## Cart Service Implementation

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

**Why a Service Layer?
**
- Keeps controllers thin and readable
- Centralizes business logic
- Improves testability
- Avoids duplication
- Makes future changes safer

**Key Methods**

```php
getCart(User $user): Cart
addProduct(User $user, Product $product, int $quantity): void
updateProductQuantity(User $user, Product $product, int $quantity): void
removeProduct(User $user, Product $product): void
checkout(User $user): Order
```

**Checkout Flow**

- Runs inside a database transaction
- Verifies stock for each item
- Creates an order and order items
- Decrements product stock
- Dispatches low-stock notifications if applicable
- Clears the user’s cart

This approach ensures **data consistency** and prevents partial checkouts.

**Low Stock Notification**

- Triggered when a product’s remaining stock drops below a configurable threshold
- Implemented as a queued job:
    ```php
    app/Jobs/LowStockNotificationJob.php
    ```
- Sends an email to the configured admin address

**Testing**

- Business logic is isolated in services and jobs
- Cart behavior can be tested without controllers
- Queue and mail can be faked in tests

**Notes**

- Prices are stored in cents to avoid floating-point issues
- Each user has exactly one cart
- All cart data is persisted per authenticated user (no session or local storage)

Author

Built by [Nnorom Ifeanyi Paul](https://github.com/ifeekz) as part of the Trustfactory Laravel Developer assessment.
