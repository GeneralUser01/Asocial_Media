<?php

namespace Database\Seeders;

use App\Models\Entry;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomPostExamplesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $examples = [
            [
                'post' => [
                    'title' => 'Whatever!',
                    'body' => 'Something',
                    'likes' => 3,
                    'dislikes' => 5,
                    // 'content_scrambler_algorithm' => 5,
                ],
                'comments' => [
                    [
                        'content' => 'I like it!',
                        'likes' => 0,
                        'dislikes' => 5,
                        // 'content_scrambler_algorithm' => 4,
                    ],
                    [
                        'content' => "Well I don't!",
                        'likes' => 2,
                        'dislikes' => 0,
                    ],
                ],
            ],
            [
                'post' => [
                    'title' => 'A nice post',
                    'body' => "I guess this nice post, it not? Now, attend more important matters, don't want my steamed hams ruined..",
                    'likes' => 3,
                    'dislikes' => 1,
                ],
                'comments' => [
                    [
                        'content' => "Yes quite nice!",
                        'likes' => 0,
                        'dislikes' => 0,
                    ],
                    [
                        'content' => "Well I don't!",
                        'likes' => 2,
                        'dislikes' => 0,
                    ],
                ],
            ],
        ];

        //
        // DO NOT CHANGE THE CODE BELOW THIS
        //
        //
        // INSTEAD MODIFY THE ARRAY ABOVE US
        //

        // And now, let's create a few posts in our database:
        foreach ($examples as $postInfo) {
            $user = null;
            if (array_key_exists('content_scrambler_algorithm', $postInfo['post'])) {
                $user = User::inRandomOrder()->where('content_scrambler_algorithm', $postInfo['post']['content_scrambler_algorithm'])->first();
            }
            if (!$user) {
                $user = User::inRandomOrder()->first();
            }

            $post = Post::factory([
                'title' => $postInfo['post']['title'],
                'body' => $postInfo['post']['body'],
            ])
                ->for($user)
                ->create();

            $entry = Entry::createForUser($user, $post);

            $likesCount = $postInfo['post']['likes'];
            $dislikesCount = $postInfo['post']['dislikes'];
            $totalLikes = $likesCount + $dislikesCount;
            $randomUsers = User::inRandomOrder()->take($totalLikes)->get();
            for ($likes = 0; $likes < $totalLikes && $likes < count($randomUsers); $likes++) {
                $randomUsers[$likes]->createLike($entry, $likes < $likesCount);
            }




            // And now, let's create a few comments for each post in our database:
            foreach ($postInfo['comments'] as $commentInfo) {
                $user = null;
                if (array_key_exists('content_scrambler_algorithm', $commentInfo)) {
                    $user = User::inRandomOrder()->where('content_scrambler_algorithm', $commentInfo['content_scrambler_algorithm'])->first();
                }
                if (!$user) {
                    $user = User::inRandomOrder()->first();
                }

                $comment = PostComment::factory([
                    'content' => $commentInfo['content'],
                ])
                    ->for($user)
                    ->for($post)
                    ->create();

                $entry = Entry::createForUser($user, $comment);

                $likesCount = $commentInfo['likes'];
                $dislikesCount = $commentInfo['dislikes'];
                $totalLikes = $likesCount + $dislikesCount;
                $randomUsers = User::inRandomOrder()->take($totalLikes)->get();
                for ($likes = 0; $likes < $totalLikes && $likes < count($randomUsers); $likes++) {
                    $randomUsers[$likes]->createLike($entry, $likes < $likesCount);
                }
            }
        }
    }
}
