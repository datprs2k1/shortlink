<?php

namespace Database\Factories;

use App\Models\Domain;
use App\Models\Shortlink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shortlink>
 */
class ShortlinkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Shortlink::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'domain_id' => Domain::factory(),
            'short_code' => strtoupper(fake()->unique()->lexify('??????')),
            'original_url' => fake()->url(),
            'title' => fake()->optional(0.3)->sentence(3),
            'description' => fake()->optional(0.5)->sentence(),
            'is_active' => fake()->boolean(85), // 85% chance of being active
            'expires_at' => fake()->optional(0.2)->dateTimeBetween('now', '+1 year'), // 20% chance of having expiration
            'click_count' => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * Create an active shortlink
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Create an inactive shortlink
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create an expired shortlink
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => fake()->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    /**
     * Create a shortlink that expires at a specific date
     */
    public function expiresAt($date): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $date,
        ]);
    }

    /**
     * Create a password protected shortlink
     */
    public function withPassword(string $password): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => $password,
        ]);
    }

    /**
     * Create a shortlink with a specific title
     */
    public function withTitle(string $title): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $title,
        ]);
    }

    /**
     * Create a shortlink with a specific description
     */
    public function withDescription(string $description): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => $description,
        ]);
    }
}