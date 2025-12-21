<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;

class Film extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'genre_id',
        'duration',
        'release_year',
        'director',
        'poster_url',
        'backdrop_url',
        'video_url',
        'rating',
        'is_featured',
        'status',
        'is_trending',
        'is_popular',
        'is_hero',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'rating' => 'float',
        'is_hero' => 'boolean',
    ];

    // Relationship
    public function genre()
    {
        return $this->belongsTo(Genre::class);
    }
    public function watchHistory()
    {
        return $this->hasMany(WatchHistory::class);
    }

    protected $guarded = [];

    // ✅ POSTER URL AMAN (HANDLE SEMUA KONDISI)
    public function getPosterUrlAttribute($value)
    {
        if (!$value) {
            return asset('images/no-poster.png'); // optional fallback
        }

        // Kalau sudah URL lengkap
        if (str_starts_with($value, 'http')) {
            return $value;
        }

        // Kalau sudah /storage/xxx
        if (str_starts_with($value, '/storage')) {
            return asset($value);
        }

        // Default: posters/xxx
        return Storage::url($value);
    }

    // ✅ BACKDROP URL AMAN
    public function getBackdropUrlAttribute($value)
    {
        if (!$value) {
            return asset('images/no-backdrop.jpg'); // optional fallback
        }

        if (str_starts_with($value, 'http')) {
            return $value;
        }

        if (str_starts_with($value, '/storage')) {
            return asset($value);
        }

        return Storage::url($value);
    }
}
