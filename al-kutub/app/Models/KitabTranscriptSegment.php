<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitabTranscriptSegment extends Model
{
    use HasFactory;

    protected $table = 'kitab_transcript_segments';

    protected $fillable = [
        'kitab_id',
        'section_key',
        'transcript_type',
        'title',
        'content',
        'content_translation',
        'content_arabic',
        'language',
        'page_start',
        'page_end',
        'sort_order',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'page_start' => 'integer',
        'page_end' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function kitab()
    {
        return $this->belongsTo(Kitab::class, 'kitab_id', 'id_kitab');
    }
}
