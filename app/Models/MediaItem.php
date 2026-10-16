<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'description',
        'creator',
        'published_year',
        'cover_url',
        'external_source',
        'external_id',
    ];

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function getReviewsAvgRatingAttribute($value): ?float
    {
        return $value === null ? null : round((float) $value, 2);
    }
}
