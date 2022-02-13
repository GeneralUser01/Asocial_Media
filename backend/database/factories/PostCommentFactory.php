<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostCommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'content' => $this->faker->paragraph,
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'scrambled_content' => function (array $attributes) {
                $user = User::find($attributes['user_id']);
                return $user->scrambleText($attributes['content']);
            },
        ];
    }
}
