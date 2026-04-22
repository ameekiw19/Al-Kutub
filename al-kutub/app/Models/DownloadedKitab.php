<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DownloadedKitab extends Model
{
    use HasFactory;

    protected $table = 'downloaded_kitabs';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'kitab_id',
        'file_path',          // Path file di device
        'file_size',          // Ukuran file dalam bytes
        'downloaded_at',      // Waktu download
        'last_accessed_at',   // Terakhir dibuka
        'is_cached',          // Apakah masih di cache
        'device_info',        // Info device (JSON)
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_cached' => 'boolean',
        'device_info' => 'array',
        'downloaded_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke Kitab
    public function kitab()
    {
        return $this->belongsTo(Kitab::class, 'kitab_id', 'id_kitab');
    }
}
