<?php

namespace App\Console\Commands;

use App\Mail\DailySalesReportMail;
use App\Models\OrderItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Class SendDailySalesReport
 *
 * This command generates and sends a daily sales report email
 * to the admin containing the products sold in the last 24 hours.
 *
 * @author Nnorom Ifeanyi Paul <nnoromifeanyi@gmail.com>
 */
class SendDailySalesReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-daily-sales-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a daily report of products sold';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sales = OrderItem::query()
            ->whereDate('created_at', today())
            ->selectRaw('product_id, SUM(quantity) as total_sold')
            ->groupBy('product_id')
            ->with('product')
            ->get();

        if ($sales->isEmpty()) {
            return;
        }

        Mail::to(config('shop.admin_email'))
            ->send(new DailySalesReportMail($sales));
    }
}
