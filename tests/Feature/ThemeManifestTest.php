<?php

namespace Tests\Feature;

use App\Support\InvalidThemeManifest;
use App\Support\Site;
use App\Support\ThemeManifest;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/** A theme's theme.json declares section types of its own; a bad one fails loudly. */
class ThemeManifestTest extends TestCase
{
    private function offers(array $overrides = []): array
    {
        return array_replace([
            'label' => 'Offers',
            'fields' => ['title' => ['type' => 'text', 'label' => 'Heading']],
            'defaults' => ['title' => 'Offers', 'data' => ['items' => []]],
        ], $overrides);
    }

    private function parse(array $sections): array
    {
        return ThemeManifest::parse('acme', json_encode(['sections' => $sections]));
    }

    private function assertRejected(string $message, callable $load): void
    {
        try {
            $load();
        } catch (InvalidThemeManifest $e) {
            $this->assertStringContainsString($message, $e->getMessage());

            return;
        }

        $this->fail('The manifest was accepted.');
    }

    public function test_a_theme_without_a_manifest_declares_nothing(): void
    {
        $this->assertSame([], ThemeManifest::sections('crystal'));
        $this->assertSame([], ThemeManifest::sections(null));
    }

    public function test_a_valid_manifest_is_read_from_the_theme_folder(): void
    {
        View::addLocation(base_path('tests/Fixtures/views'));

        $this->assertContains('schematest', Site::themes());

        $types = ThemeManifest::sections('schematest');
        $this->assertSame(['offers'], array_keys($types));
        $this->assertSame('Offers', $types['offers']['label']);
        $this->assertSame('list', $types['offers']['fields']['data.items']['type']);
        $this->assertSame('text', $types['offers']['fields']['data.items']['item']['features']['item']);
        $this->assertTrue($types['offers']['fields']['data.items']['item']['name']['required']);
        // Select options come back in one shape, whichever way they were written.
        $this->assertSame([['value' => 'grid', 'label' => 'grid'], ['value' => 'list', 'label' => 'list']],
            $types['offers']['fields']['data.layout']['options']);
        $this->assertSame(['title' => 'Offers', 'content' => '', 'data' => ['intro' => '', 'layout' => 'grid', 'items' => []]],
            $types['offers']['defaults']);
    }

    public function test_bad_json_names_the_theme(): void
    {
        $root = storage_path('framework/testing/views-'.uniqid());
        File::ensureDirectoryExists("{$root}/site/themes/broken");
        File::put("{$root}/site/themes/broken/theme.json", '{"sections": {');
        View::addLocation($root);

        try {
            $this->assertRejected('Theme "broken": theme.json is not valid JSON', fn () => ThemeManifest::sections('broken'));
        } finally {
            File::deleteDirectory($root);
        }
    }

    public function test_a_built_in_type_cannot_be_redeclared(): void
    {
        $this->assertRejected('Theme "acme", section type "hero": this is a built-in section type',
            fn () => $this->parse(['hero' => $this->offers()]));
    }

    public function test_defaults_are_required(): void
    {
        $offers = $this->offers();
        unset($offers['defaults']);

        $this->assertRejected('Theme "acme", section type "offers": "defaults" are required',
            fn () => $this->parse(['offers' => $offers]));
    }

    public function test_an_unknown_field_type_is_named(): void
    {
        $this->assertRejected('Theme "acme", section type "offers": field "data.items.colour" has unknown type "colour"',
            fn () => $this->parse(['offers' => $this->offers(['fields' => [
                'data.items' => ['type' => 'list', 'label' => 'Offer', 'item' => [
                    'colour' => ['type' => 'colour', 'label' => 'Colour'],
                ]],
            ]])]));
    }

    public function test_type_names_and_field_paths_are_checked(): void
    {
        $this->assertRejected('section type "Offers": a type name must be',
            fn () => $this->parse(['Offers' => $this->offers()]));

        $this->assertRejected('field "items" must be title, content or data.<key>',
            fn () => $this->parse(['offers' => $this->offers(['fields' => ['items' => ['type' => 'text', 'label' => 'Items']]])]));

        $this->assertRejected('field "data.kind": a select needs "options"',
            fn () => $this->parse(['offers' => $this->offers(['fields' => ['data.kind' => ['type' => 'select', 'label' => 'Kind']]])]));

        $this->assertRejected('field "data.items": a list needs "item"',
            fn () => $this->parse(['offers' => $this->offers(['fields' => ['data.items' => ['type' => 'list', 'label' => 'Items']]])]));
    }

    public function test_a_field_can_be_kept_off_the_page(): void
    {
        $types = $this->parse(['offers' => $this->offers(['fields' => [
            'data.photo_alt' => ['type' => 'text', 'label' => 'Photo description', 'preview' => false],
        ]])]);

        $this->assertFalse($types['offers']['fields']['data.photo_alt']['preview']);

        $this->assertRejected('field "data.photo_alt": "preview" must be true or false',
            fn () => $this->parse(['offers' => $this->offers(['fields' => [
                'data.photo_alt' => ['type' => 'text', 'label' => 'Photo description', 'preview' => 'no'],
            ]])]));
    }
}
