<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Draft extends Model
{
    use HasFactory;

    protected $fillable = [
        'bot_id',
        'user_id',
        'type',
        'file_id',
        'file_path',
        'caption',
        'price',
        'category',
        'telegram_message_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function bot()
    {
        return $this->belongsTo(Bot::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}