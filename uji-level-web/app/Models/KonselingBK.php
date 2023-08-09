<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KonselingBK extends Model
{
    use HasFactory;

    protected $fillable = [
        'layanan_id', 
        'guru_id',
        'walas_id',
        'judul',
        'tujuan',
        'status',
        'jadwal_konseling',
        'hasil_konseling'
    ];

    protected $table = 'konseling_bk';

    public function layanan(){
        return $this->belongsTo(LayananBK::class, 'layanan_id', 'id');
    }

    public function guru(){
        return $this->belongsTo(Guru::class, 'guru_id', 'id');
    }

    public function walas(){
        return $this->belongsTo(Walas::class, 'walas_id', 'id');
    }

    public function siswa()
    {
        return $this->belongsToMany(Siswa::class, 'pivot_bk', 'konseling_id', 'siswa_id');
    }
}
