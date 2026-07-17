<?php

namespace Tests\Feature;

use Tests\TestCase;

class FrontendAssetBuildTest extends TestCase
{
    public function test_layouts_use_the_local_vite_pipeline_without_tailwind_or_alpine_cdns(): void
    {
        foreach (['app', 'admin', 'auth'] as $layout) {
            $source = file_get_contents(resource_path("views/layouts/{$layout}.blade.php"));

            $this->assertStringContainsString('@vite', $source);
            $this->assertStringNotContainsString('cdn.tailwindcss.com', $source);
            $this->assertStringNotContainsString('cdn.jsdelivr.net/npm/alpinejs', $source);
        }

        $menu = file_get_contents(resource_path('views/partials/menu.blade.php'));
        $this->assertStringNotContainsString('alpinejs@', $menu);
        $this->assertStringContainsString("import Alpine from 'alpinejs'", file_get_contents(resource_path('js/app.js')));
        $this->assertStringNotContainsString("import './bootstrap'", file_get_contents(resource_path('js/app.js')));
    }

    public function test_tailwind_source_contains_design_tokens_and_no_manual_public_stylesheet_remains(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('@import "tailwindcss"', $css);
        $this->assertStringContainsString('--casino-bg: #060812', $css);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        $this->assertFileDoesNotExist(public_path('css/app.css'));
    }

    public function test_slot_atlases_are_served_in_an_optimized_format(): void
    {
        foreach (['olympus', 'sweet', 'book', 'starburst', 'bass'] as $atlas) {
            $path = public_path("images/slots/{$atlas}-symbols-v2.webp");

            $this->assertFileExists($path);
            $this->assertLessThan(400_000, filesize($path));
        }
    }

    public function test_google_tag_is_loaded_immediately_after_every_web_layout_head(): void
    {
        foreach (['app', 'admin', 'auth'] as $layout) {
            $source = file_get_contents(resource_path("views/layouts/{$layout}.blade.php"));

            $this->assertStringContainsString("<head>\n    @include('partials.google-tag')", $source);
        }

        $tag = file_get_contents(resource_path('views/partials/google-tag.blade.php'));
        $this->assertSame(2, substr_count($tag, 'G-ZG7EW2QE96'));
        $this->assertStringContainsString('https://www.googletagmanager.com/gtag/js', $tag);
        $this->assertStringContainsString("gtag('config', 'G-ZG7EW2QE96')", $tag);
    }
}
