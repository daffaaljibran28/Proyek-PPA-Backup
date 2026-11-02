<?php

namespace Database\Seeders;

use App\Models\Informasi;
use Illuminate\Database\Seeder;

class InformasiSeeder extends Seeder
{
    public function run(): void
    {
        $informasis = [
            [
                'judul' => 'Mengenali Kekerasan dan Mencari Bantuan',
                'konten' => "Kekerasan dapat berbentuk fisik, psikis, seksual, penelantaran, atau bentuk lain yang merugikan. Jika Anda atau seseorang berada dalam bahaya, utamakan keselamatan dan cari tempat yang lebih aman.\n\nHubungi orang yang dipercaya atau layanan bantuan. Simpan informasi atau bukti hanya jika aman dilakukan, dan jangan menyebarkan identitas maupun cerita korban tanpa izin.",
            ],
            [
                'judul' => 'Cara Mendampingi Korban dengan Aman',
                'konten' => "Dengarkan cerita tanpa menyalahkan atau menghakimi. Berikan ruang bagi korban untuk menentukan langkah yang terasa aman baginya.\n\nJaga kerahasiaan identitas dan informasi pribadi. Tawarkan bantuan untuk menghubungi orang tepercaya atau layanan profesional, tanpa memaksa korban mengambil keputusan.",
            ],
        ];

        foreach ($informasis as $informasi) {
            Informasi::updateOrCreate(
                ['judul' => $informasi['judul']],
                [
                    'konten' => $informasi['konten'],
                    'status' => 'published',
                    'tanggal_publish' => today(),
                ]
            );
        }
    }
}
