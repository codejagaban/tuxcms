<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'title' => rtrim($title, '.'),
            'slug' => Str::slug($title),
            'excerpt' => fake()->sentence(),
            'content' => fake()->paragraph(),
            'template' => 'default',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'is_homepage' => false,
            'show_in_nav' => true,
            'author_id' => User::factory(),
        ];
    }

    public function homepage(): static
    {
        return $this->state(['is_homepage' => true, 'slug' => 'home', 'title' => 'Home']);
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft', 'published_at' => null]);
    }

    /** Published in the future — should not appear on the site yet. */
    public function scheduled(): static
    {
        return $this->state(['status' => 'published', 'published_at' => now()->addWeek()]);
    }

    public function hiddenFromNav(): static
    {
        return $this->state(['show_in_nav' => false]);
    }
}
