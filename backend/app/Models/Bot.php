<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bot extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'token',
        'username',
        'webhook_url',
        'backup_channel_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
}