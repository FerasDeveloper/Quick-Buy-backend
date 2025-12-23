<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Wallet;
use Carbon\Carbon;

class UpdateExpiredOffers extends Command
{
    protected $signature = 'offer:updateExpired';
    protected $description = 'Update offer_id and posts_number for expired offers after 30 days';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // تحديد تاريخ انتهاء الاشتراك (قبل ثلاثين يومًا)
        // $expirationDate = Carbon::now()->subDays(30);
        $expirationDate = Carbon::now()->addHour(1);

        // تحديث المستخدمين الذين مر ثلاثين يومًا على اشتراكهم
        Wallet::whereDate('updated_at', '<=', $expirationDate)
            ->where('offerId', '!=', 4) // التأكد من أن العرض الحالي ليس "بدون عرض"
            ->update([
                'offerId' => 4,
                'postsNumber' => 0, 
            ]);

        $this->info('Expired offers updated successfully.');
    }
}
