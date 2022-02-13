<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'body' => implode("\n", $this->faker->paragraphs(10, false)),
            'user_id' => User::factory(),
            'scrambled_body' =>  function (array $attributes) {
                $user = User::find($attributes['user_id']);
                return $user->scrambleText($attributes['body']);
            },
        ];
    }
}
