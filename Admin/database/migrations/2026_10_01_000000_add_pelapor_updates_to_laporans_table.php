<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan', function (Blueprint $table) {
            if (!Schema::hasColumn('laporan', 'riwayat_status')) {
                $table->json('riwayat_status')->nullable();
            }

            if (!Schema::hasColumn('laporan', 'pesan_pelapor')) {
                $table->text('pesan_pelapor')->nullable();
            }
        });

        DB::table('laporan')
            ->whereNull('riwayat_status')
            ->orderBy('id_laporan')
            ->chunkById(100, function ($laporans): void {
                foreach ($laporans as $laporan) {
                    DB::table('laporan')
                        ->where('id_laporan', $laporan->id_laporan)
                        ->update([
                            'riwayat_status' => json_encode([[
                                'status' => $laporan->status_penanganan ?: 'Menunggu Verifikasi',
                                'pesan' => null,
                                'pada' => $laporan->updated_at ?: $laporan->created_at,
                                'oleh' => 'Sistem',
                            ]]),
                        ]);
                }
            }, 'id_laporan');
    }

    public function down(): void
    {
        Schema::table('laporan', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('laporan', 'riwayat_status')) {
                $columns[] = 'riwayat_status';
            }
            if (Schema::hasColumn('laporan', 'pesan_pelapor')) {
                $columns[] = 'pesan_pelapor';
            }
            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};