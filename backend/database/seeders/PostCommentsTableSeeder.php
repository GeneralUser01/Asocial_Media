<?php

namespace Database\Seeders;

use App\Models\Entry;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostCommentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Let's truncate our existing records to start from scratch.
        // PostComment::truncate();
        PostComment::query()->delete();

        foreach (Post::all() as $post) {

            // And now, let's create a few comments for each post in our database:
            $commentCount = rand(5, 9);
            for ($i = 0; $i < $commentCount; $i++) {
                $user = User::inRandomOrder()->first();

                $comment = PostComment::factory()
                    ->for($user)
                    ->for($post)
                    ->create();

                $entry = Entry::createForUser($user, $comment);

                if (rand(0, 3) === 0) {
                    $likesCount = rand(0, 3);
                    for ($likes = 0; $likes < $likesCount; $likes++) {
                        User::inRandomOrder()->first()->createLike($entry, rand(0, 2) !== 0);
                    }
                }
            }
        }
    }
}
