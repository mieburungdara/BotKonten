<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'anonymous_id',
        'telegram_id',
        'name',
        'language',
        'theme',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function drafts()
    {
        return $this->hasMany(Draft::class);
    }

    public function albums()
    {
        return $this->hasMany(Album::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges');
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }
}