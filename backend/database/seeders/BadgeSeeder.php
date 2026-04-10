<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BadgeSeeder extends Seeder
{
    public function run()
    {
        $badges = [
            [
                'name' => 'First Sale',
                'description' => 'Congratulations on your first sale!',
                'icon' => '🏆',
                'type' => 'first_sale',
                'threshold' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Top Seller',
                'description' => 'Sold 10 items - You\'re a top seller!',
                'icon' => '⭐',
                'type' => 'top_seller',
                'threshold' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'High Rated',
                'description' => 'Average rating of 4.5+ - Quality seller!',
                'icon' => '🌟',
                'type' => 'high_rated',
                'threshold' => 4.5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Content Creator',
                'description' => 'Created 20 media items - Pro creator!',
                'icon' => '🎨',
                'type' => 'content_creator',
                'threshold' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Reviewer',
                'description' => 'Left 5 reviews - Helpful community member!',
                'icon' => '📝',
                'type' => 'reviewer',
                'threshold' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('badges')->insert($badges);
    }
}