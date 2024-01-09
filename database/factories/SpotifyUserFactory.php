<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SpotifyUser>
 */
class SpotifyUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'spotify_id' => $this->faker->uuid,
            'access_token' => $this->faker->sha256,
            'refresh_token' => $this->faker->sha256,
            'token_expire' => $this->faker->numberBetween(0, 3600),
        ];
    }
}
