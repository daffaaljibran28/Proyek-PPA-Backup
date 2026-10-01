<?php

namespace App\Providers;

use App\Models\Laporan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['pelapor.layout', 'pelapor.dashboard'], function ($view): void {
            $user = auth()->user();
            $notifications = collect();

            if ($user && strtolower((string) $user->role) === 'pengguna') {
                $state = Cache::get('pelapor-notifications:' . $user->id, []);
                $readNotifications = is_array($state['read'] ?? null) ? $state['read'] : [];
                $deletedNotifications = is_array($state['deleted'] ?? null) ? $state['deleted'] : [];

                $notifications = Laporan::where('id_pengguna', $user->id)
                    ->whereNotNull('riwayat_status')
                    ->latest('updated_at')
                    ->get(['id_laporan', 'nomor_laporan', 'kategori', 'riwayat_status'])
                    ->flatMap(function (Laporan $laporan) use ($readNotifications, $deletedNotifications): array {
                        return collect($laporan->riwayat_status ?? [])->map(function (array $entry, int $eventIndex) use ($laporan, $readNotifications, $deletedNotifications): array {
                            $notificationKey = $laporan->id_laporan . ':' . $eventIndex;

                            return $entry + [
                                'id_laporan' => $laporan->id_laporan,
                                'event_index' => $eventIndex,
                                'nomor_laporan' => $laporan->nomor_laporan,
                                'kategori' => $laporan->kategori,
                                'read' => isset($readNotifications[$notificationKey]),
                                'deleted' => isset($deletedNotifications[$notificationKey]),
                            ];
                        })->all();
                    })
                    ->sortByDesc('pada')
                    ->reject(fn (array $notification): bool => $notification['deleted'])
                    ->values();
            }

            $view->with('pelaporNotifications', $notifications);
        });
    }
}
