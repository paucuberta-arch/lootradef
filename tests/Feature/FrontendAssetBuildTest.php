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

    public function test_heavy_chart_library_is_loaded_only_on_the_analytics_page(): void
    {
        $javascript = file_get_contents(resource_path('js/app.js'));
        $charts = file_get_contents(resource_path('views/admin/charts.blade.php'));

        $this->assertStringNotContainsString("import Chart from 'chart.js/auto'", $javascript);
        $this->assertStringContainsString("import('chart.js/auto')", $javascript);
        $this->assertStringContainsString('data-admin-charts', $charts);
        $this->assertStringContainsString('lootra:charts-ready', $charts);
    }

    public function test_home_visuals_have_lighter_responsive_variants(): void
    {
        foreach ([
            '01_heroes/hero_home_lootra_960x450.webp' => '01_heroes/hero_home_lootra_1920x900.webp',
            '01_heroes/hero_casino_roulette_960x450.webp' => '01_heroes/hero_casino_roulette_1920x900.webp',
            '02_promo_banners/banner_slots_nuevos_960x300.webp' => '02_promo_banners/banner_slots_nuevos_1920x600.webp',
            '02_promo_banners/banner_originals_neon_960x300.webp' => '02_promo_banners/banner_originals_neon_1920x600.webp',
            '02_promo_banners/banner_casino_en_vivo_960x300.webp' => '02_promo_banners/banner_casino_en_vivo_1920x600.webp',
        ] as $responsive => $original) {
            $responsivePath = public_path("images/lootra_visual_pack/{$responsive}");
            $originalPath = public_path("images/lootra_visual_pack/{$original}");

            $this->assertFileExists($responsivePath);
            $this->assertLessThan(filesize($originalPath), filesize($responsivePath));
        }

        foreach (range(1, 9) as $separator) {
            $path = public_path(sprintf('images/lootra_visual_pack/10_decorative/separator_energy_%02d_1600x260.webp', $separator));
            $this->assertFileExists($path);
            $this->assertLessThan(80_000, filesize($path));
        }
    }

    public function test_case_and_sports_visuals_are_local_and_optimized(): void
    {
        foreach (['case_starter.webp', 'case_gaming.webp', 'case_tech.webp', 'case_luxury.webp'] as $asset) {
            $path = public_path("images/lootra_visual_pack/12_cases/{$asset}");
            $this->assertFileExists($path);
            $this->assertLessThan(150_000, filesize($path));
        }

        foreach (['sports_hero_960x540.webp', 'sports_stadium_1200x675.webp', 'sports_ball_768x512.webp'] as $asset) {
            $path = public_path("images/lootra_visual_pack/13_sports/{$asset}");
            $this->assertFileExists($path);
            $this->assertLessThan(80_000, filesize($path));
        }
    }

    public function test_google_tag_is_loaded_immediately_after_every_web_layout_head(): void
    {
        foreach (['app', 'admin', 'auth'] as $layout) {
            $source = file_get_contents(resource_path("views/layouts/{$layout}.blade.php"));

            $this->assertStringContainsString("<head>\n    @include('partials.google-tag')", $source);
        }

        config()->set('services.google_analytics.measurement_id', 'G-TEST123456');
        config()->set('services.google_analytics.debug', true);
        session()->flash('ga_reto_iniciado', true);

        $tag = view('partials.google-tag')->render();

        $this->assertStringContainsString(
            '<script async src="https://www.googletagmanager.com/gtag/js?id=G-TEST123456"></script>',
            $tag
        );
        $this->assertMatchesRegularExpression('/<\/script>\s*<script>\s*\(\(\) =>/', $tag);
        $this->assertStringContainsString("gtag('config', measurementId, configParameters)", $tag);
        $this->assertStringContainsString("gtag('event', 'reto_iniciado', eventParameters)", $tag);
        $this->assertStringContainsString("reto_id: 'reto_1'", $tag);
        $this->assertStringContainsString("reto_nombre: 'RickyEditXLootra'", $tag);
        $this->assertStringContainsString("const analyticsDebugEnabled = true", $tag);
        $this->assertStringContainsString("localStorage.getItem(sentKey)", $tag);
        $this->assertStringContainsString("localStorage.setItem(pendingKey, 'true')", $tag);
        $this->assertStringContainsString('event_callback: markChallengeStartAsSent', $tag);
        $this->assertStringContainsString('send_to: measurementId', $tag);
    }
}
