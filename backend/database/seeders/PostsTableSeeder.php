<?php

namespace Database\Seeders;

use App\Models\Entry;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Let's truncate our existing records to start from scratch.
        Post::query()->delete();

        // And now, let's create a few posts in our database:
        for ($i = 0; $i < 30; $i++) {
            $user = User::inRandomOrder()->first();

            $post = Post::factory()
                ->for($user)
                ->create();

            $entry = Entry::createForUser($user, $post);

            $likesCount = rand(0, 5);
            for ($likes = 0; $likes < $likesCount; $likes++) {
                User::inRandomOrder()->first()->createLike($entry, rand(0, 3) !== 0);
            }
        }
    }
}
