<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitabRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'kitab_id',
        'version_no',
        'action',
        'old_data',
        'new_data',
        'changed_fields',
        'old_file_pdf',
        'old_cover',
        'actor_id',
        'note',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'changed_fields' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function kitab()
    {
        return $this->belongsTo(Kitab::class, 'kitab_id', 'id_kitab');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id', 'id');
    }
}
