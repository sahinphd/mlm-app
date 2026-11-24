<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'image',
        'category',
        'author_id',
        'author_name',
        'status',
        'published_at',
        'views',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'views' => 'integer',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Accessor for full image URL.
     */
    public function getImageAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, 'data:')) {
            return $value;
        }
        return asset($value);
    }

    /**
     * Helper for display author name.
     */
    public function getDisplayAuthorAttribute(): string
    {
        if (!empty($this->author_name)) {
            return $this->author_name;
        }
        if ($this->author) {
            return $this->author->name;
        }
        return 'Dokandar Editorial Team';
    }

    /**
     * Calculate estimated reading time in minutes.
     */
    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content ?? ''));
        return max(1, (int) ceil($wordCount / 200));
    }

    /**
     * Get effective SEO Title.
     */
    public function getSeoTitleAttribute(): string
    {
        return !empty($this->meta_title) ? $this->meta_title : ($this->title . ' | ' . config('app.name', 'Dua Re Dokandar'));
    }

    /**
     * Get effective SEO Description.
     */
    public function getSeoDescriptionAttribute(): string
    {
        if (!empty($this->meta_description)) {
            return $this->meta_description;
        }
        if (!empty($this->excerpt)) {
            return Str::limit(strip_tags($this->excerpt), 160);
        }
        return Str::limit(strip_tags($this->content), 160);
    }

    /**
     * Scope for published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Generate unique slug from title.
     */
    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $original . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
