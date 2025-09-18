<?php

namespace Database\Factories;

use App\Models\Click;
use App\Models\Shortlink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Click>
 */
class ClickFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Click::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shortlink_id' => Shortlink::factory(),
            'ip_address' => fake()->optional(0.8)->ipv4(),
            'user_agent' => fake()->optional(0.9)->userAgent(),
            'referer' => fake()->optional(0.3)->url(),
            'country_code' => fake()->optional(0.7)->countryCode(),
            'clicked_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Create a click with a specific IP address
     */
    public function withIpAddress(string $ipAddress): static
    {
        return $this->state(fn (array $attributes) => [
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Create a click with a specific user agent
     */
    public function withUserAgent(string $userAgent): static
    {
        return $this->state(fn (array $attributes) => [
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Create a click from a specific country
     */
    public function fromCountry(string $countryCode): static
    {
        return $this->state(fn (array $attributes) => [
            'country_code' => $countryCode,
        ]);
    }

    /**
     * Create a click with a specific referer
     */
    public function withReferer(string $referer): static
    {
        return $this->state(fn (array $attributes) => [
            'referer' => $referer,
        ]);
    }

    /**
     * Create a click from today
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'clicked_at' => fake()->dateTimeBetween('today', 'now'),
        ]);
    }

    /**
     * Create a click at a specific time
     */
    public function clickedAt($dateTime): static
    {
        return $this->state(fn (array $attributes) => [
            'clicked_at' => $dateTime,
        ]);
    }

    /**
     * Create a click from this week
     */
    public function thisWeek(): static
    {
        return $this->state(fn (array $attributes) => [
            'clicked_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Create a click from this month
     */
    public function thisMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'clicked_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Create a click with mobile user agent
     */
    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15',
        ]);
    }

    /**
     * Create a click with desktop user agent
     */
    public function desktop(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        ]);
    }
}