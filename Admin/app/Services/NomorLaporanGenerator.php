<?php

namespace App\Services;

use App\Models\Laporan;

class NomorLaporanGenerator
{
    public function generate(): string
    {
        $tanggal = now()->format('Ymd');
        $prefix = 'LAP-'.$tanggal.'-';
        $urutan = Laporan::whereDate('created_at', today())
            ->where('nomor_laporan', 'like', $prefix.'%')
            ->count() + 1;

        return $prefix.str_pad((string) $urutan, 3, '0', STR_PAD_LEFT);
    }
}
