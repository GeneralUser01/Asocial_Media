<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostComment;
use Illuminate\Database\Seeder;

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
        PostComment::truncate();

        $faker = \Faker\Factory::create();

        foreach (Post::all() as $post) {

            // And now, let's create a few comments for each post in our database:
            for ($i = 0; $i < 8; $i++) {
                $comment = new PostComment([
                    'content' => $faker->paragraph,
                ]);
                $comment->post_id = $post->id;
                $comment->save();
            }
        }

    }
}
