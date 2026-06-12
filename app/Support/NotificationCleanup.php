<?php

namespace App\Support;

use App\Models\AssetPurchase;
use App\Models\DebtReceivable;
use App\Models\FinanceTransaction;
use App\Models\MaterialPurchase;
use App\Models\RevenueSharing;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationCleanup
{
    public static function markResolvedApprovalNotificationsRead(?User $user): void
    {
        if (! $user) {
            return;
        }

        $now = now();
        $notificationIds = $user->unreadNotifications()
            ->where('data', 'like', '%"title":"Approval%')
            ->get(['id', 'data'])
            ->filter(function ($notification) {
                $data = $notification->data;
                $url = is_array($data) ? ($data['url'] ?? null) : null;

                return $url && self::approvalIsResolved($url);
            })
            ->pluck('id')
            ->all();

        if (empty($notificationIds)) {
            return;
        }

        DB::table('notifications')
            ->whereIn('id', $notificationIds)
            ->update(['read_at' => $now, 'updated_at' => $now]);
    }

    private static function approvalIsResolved(string $url): bool
    {
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $segments = array_values(array_filter(explode('/', $path)));
        $key = urldecode((string) end($segments));

        if ($key === '') {
            return false;
        }

        if (Str::contains($path, 'keuangan/transaksi/')) {
            return FinanceTransaction::whereKey($key)
                ->where('status', '!=', 'menunggu_approval')
                ->exists();
        }

        if (Str::contains($path, 'operasional/pembelian-material/')) {
            return MaterialPurchase::where('invoice_number', $key)
                ->where('status', '!=', 'menunggu_approval')
                ->exists();
        }

        if (Str::contains($path, 'keuangan/pembelian-aset/')) {
            return AssetPurchase::whereKey($key)
                ->where('status', '!=', 'menunggu_approval')
                ->exists();
        }

        if (Str::contains($path, 'keuangan/hutang-piutang/')) {
            return DebtReceivable::whereKey($key)
                ->where('status', '!=', 'menunggu_approval')
                ->exists();
        }

        if (Str::contains($path, 'keuangan/revenue-sharing/')) {
            return RevenueSharing::whereKey($key)
                ->where('status', '!=', 'menunggu_approval')
                ->exists();
        }

        return false;
    }
}
