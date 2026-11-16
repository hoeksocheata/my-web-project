<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\User;
use App\Notifications\LowStockAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class NotifyLowStock extends Command
{
    protected $signature = 'books:low-stock';

    protected $description = 'ផ្ញើអ៊ីមែលជូនដំណឹង Admin អំពីសៀវភៅដែលជិតអស់ស្តុក';

    public function handle(): int
    {
        $books = Book::where('stock', '<=', config('bookstore.low_stock'))->orderBy('stock')->get();

        if ($books->isEmpty()) {
            $this->info('គ្មានសៀវភៅជិតអស់ស្តុកទេ។');
            return self::SUCCESS;
        }

        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new LowStockAlert($books));

        $this->info("បានផ្ញើការជូនដំណឹងអំពីសៀវភៅ {$books->count()} ក្បាល ទៅ Admin {$admins->count()} នាក់។");

        return self::SUCCESS;
    }
}
