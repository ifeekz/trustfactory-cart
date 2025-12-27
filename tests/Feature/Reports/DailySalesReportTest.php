<?php

namespace Tests\Feature\Reports;

use App\Mail\DailySalesReportMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Class DailySalesReportTest
 *
 * This class contains tests for the daily sales report functionality.
 *
 * @author Nnorom Ifeanyi Paul <nnoromifeanyi@gmail.com>
 */
class DailySalesReportTest extends TestCase
{
/**
 * Tests that the daily sales report email is sent.
 *
 * This test creates an order and an order item, then runs the daily sales report
 * command. It asserts that the DailySalesReportMail is sent.
 *
 * @return void
 */
    public function test_daily_sales_report_email_is_sent(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'total' => 1000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'price' => 1000,
            'quantity' => 2,
        ]);

        $this->artisan('app:send-daily-sales-report');

        Mail::assertSent(DailySalesReportMail::class);
    }
}
