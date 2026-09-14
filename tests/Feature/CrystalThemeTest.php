<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use App\Services\Site\SiteBuilder;
use Database\Seeders\CrystalContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CrystalThemeTest extends TestCase
{
    use RefreshDatabase;

    private string $output;

    protected function setUp(): void
    {
        parent::setUp();

        $this->output = storage_path('framework/testing/crystal-'.uniqid());
        File::ensureDirectoryExists($this->output);
        config(['site.output_path' => $this->output, 'site.url' => 'https://crystal.test']);

        User::factory()->create();
        $this->seed(CrystalContentSeeder::class);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->output);
        parent::tearDown();
    }

    public function test_crystal_import_creates_the_expected_page_tree(): void
    {
        $this->assertSame(5, Page::count());
        $this->assertSame(['Home', 'About', 'Services', 'Salon', 'Contact'], Page::orderBy('order')->pluck('title')->all());
        $this->assertTrue(Page::where('is_homepage', true)->sole()->template === 'crystal');
        $this->assertTrue(Page::query()->where('template', '!=', 'crystal')->doesntExist());
    }

    public function test_crystal_theme_builds_static_pages_with_namespaced_assets_and_seo(): void
    {
        $result = app(SiteBuilder::class)->build();

        $this->assertSame(5, $result['pages']);
        $this->assertFileExists($this->output.'/index.html');
        $this->assertFileExists($this->output.'/salon/index.html');
        $this->assertFileExists($this->output.'/contact/index.html');

        $home = File::get($this->output.'/index.html');
        $this->assertStringContainsString('A crystal cleaning you can trust', $home);
        $this->assertStringContainsString('/themes/crystal/css/style.css?v=3', $home);
        $this->assertStringContainsString('https://crystal.test/', $home);
        $this->assertStringContainsString('Crystal Services Limited', $home);

        $contact = File::get($this->output.'/contact/index.html');
        $this->assertStringContainsString('name="access_key"', $contact);
        $this->assertStringContainsString('id="contact_form"', $contact);
        $this->assertStringContainsString('56 Northfield Street', $contact);
    }
}
