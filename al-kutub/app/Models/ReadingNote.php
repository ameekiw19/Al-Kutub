<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kitab_id',
        'note_content',
        'page_number',
        'highlighted_text',
        'note_color',
        'is_private',
        'client_request_id',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the note.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the kitab that the note belongs to.
     */
    public function kitab(): BelongsTo
    {
        return $this->belongsTo(Kitab::class, 'kitab_id', 'id_kitab');
    }

    /**
     * Scope a query to only include public notes.
     */
    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    /**
     * Scope a query to only include private notes.
     */
    public function scopePrivate($query)
    {
        return $query->where('is_private', true);
    }

    /**
     * Scope a query to filter by kitab.
     */
    public function scopeForKitab($query, $kitabId)
    {
        return $query->where('kitab_id', $kitabId);
    }

    /**
     * Scope a query to filter by user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get formatted creation date.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d M Y, H:i');
    }

    /**
     * Get note preview (first 100 characters).
     */
    public function getPreviewAttribute(): string
    {
        return strlen($this->note_content) > 100 
            ? substr($this->note_content, 0, 100) . '...' 
            : $this->note_content;
    }
}
