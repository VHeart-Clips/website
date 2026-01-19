<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Clip\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        // One time Setup
        if (Tag::count() > 0) {
            return;
        }

        $tags = [
            'Comedy Gold',
            'Lost Moment',
            'Epic Fail',
            'Perfect Timing',
            'Epic Win',
            'Rage-Mode',
            'Skilled Moment',
            'Jumpscare',
            'Wholesome',
            'Realtalk',
            'Tech Fail',
            'Bug',
            'Kreativ',
            'Epic Win',
            'Sprachfehler',
            'Chat Interaktion',
            'Weisheiten',
            'Storytime',
            'Was passiert gerade?',
            'Musik',
        ];

        foreach ($tags as $tag) {
            Tag::create([
                'name' => $tag,
            ]);
        }
    }
}
