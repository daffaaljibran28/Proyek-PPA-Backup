<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Informasi extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit jika diperlukan
    protected $table = 'informasis';

    // Kolom yang diizinkan untuk diisi
    protected $fillable = [
        'judul',
        'konten',
        'status',
        'tanggal_publish',
        'gambar', 
        
    ];
}