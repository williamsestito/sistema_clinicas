<?php

namespace App\Providers;

use App\Models\WhatsAppMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('partials.navbar', function ($view) {
            $waStats = (object) ['sent' => 0, 'failed' => 0];

            if (Auth::check() && Auth::user()->tenant_id) {
                $tenantId = Auth::user()->tenant_id;
                $today    = Carbon::today();

                $row = WhatsAppMessage::where('tenant_id', $tenantId)
                    ->whereDate('created_at', $today)
                    ->selectRaw("
                        SUM(CASE WHEN status IN ('sent','delivered','read') THEN 1 ELSE 0 END) as sent,
                        SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
                    ")
                    ->first();

                if ($row) {
                    $waStats->sent   = (int) $row->sent;
                    $waStats->failed = (int) $row->failed;
                }
            }

            $view->with('waStats', $waStats);
        });
    }
}
