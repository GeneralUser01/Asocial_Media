<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

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
            Post::factory()
                ->for(User::inRandomOrder()->first())
                ->create();
        }
    }
}
