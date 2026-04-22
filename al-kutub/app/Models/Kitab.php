<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Kitab extends Model
{
    protected $table = 'kitab';
    protected $primaryKey = 'id_kitab';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'judul',
        'penulis',
        'deskripsi',
        'kategori',
        'bahasa',
        'file_pdf' ,
        'cover',    
        'views',
        'downloads',
        'viewed_by',
        'publication_status',
        'reviewed_at',
        'reviewed_by',
        'published_at',
        'published_by',
        'status_note',
    ];

    protected $casts = [
        'views' => 'integer',
        'downloads' => 'integer',
        'viewed_by' => 'json', // Use json instead of array
        'reviewed_at' => 'datetime',
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // ===== RELATIONSHIPS =====
    public function ratings()
    {
        return $this->hasMany(Rating::class, 'id_kitab', 'id_kitab');
    }

    public function revisions()
    {
        return $this->hasMany(KitabRevision::class, 'kitab_id', 'id_kitab');
    }

    public function transcriptSegments()
    {
        return $this->hasMany(KitabTranscriptSegment::class, 'kitab_id', 'id_kitab');
    }

    public function averageRating()
    {
        return round($this->ratings()->avg('rating') ?? 0, 1);
    }

    public function ratingsCount()
    {
        return $this->ratings()->count();
    }

    public function scopePublished($query)
    {
        return $query->where('publication_status', 'published');
    }

    public function isPublished(): bool
    {
        return $this->publication_status === 'published';
    }

    public function isInReview(): bool
    {
        return $this->publication_status === 'review';
    }

    public function isDraft(): bool
    {
        return $this->publication_status === 'draft';
    }

    protected static function boot()
    {
        parent::boot();

        // Cleanup related records when a kitab is deleted
        static::deleting(function ($kitab) {
            // Delete related histories
            \App\Models\History::where('kitab_id', $kitab->id_kitab)->delete();
            
            // Delete related bookmarks
            \App\Models\Bookmark::where('id_kitab', $kitab->id_kitab)->delete();
            
            // Delete related comments (assuming Comment model exists and has kitab_id or similar)
            if (class_exists('\App\Models\Comment')) {
                \App\Models\Comment::where('id_kitab', $kitab->id_kitab)->delete();
            }

            // Delete related notifications referencing this kitab
            // Note: action_url like "/kitab/{id}"
            \App\Models\AppNotification::where('action_url', 'like', "%/kitab/{$kitab->id_kitab}%")->delete();

            if (Schema::hasTable('kitab_transcript_segments')) {
                $kitab->transcriptSegments()->delete();
            }
        });
    }
}
