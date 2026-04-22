<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DownloadLog extends Model
{
    use HasFactory;

    protected $table = 'download_logs';

    protected $fillable = [
        'kitab_id',
        'user_id',
    ];

    public function kitab()
    {
        return $this->belongsTo(Kitab::class, 'kitab_id', 'id_kitab');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
