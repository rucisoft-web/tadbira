<?php

namespace Tests\Feature;

use App\Support\UserGuide;
use Tests\TestCase;

class GuideTest extends TestCase
{
    public function test_guide_page_is_public_and_lists_chapters(): void
    {
        $this->get('/panduan')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Guide')
                ->where('sections.0.id', 'pengantar')
                ->where('sections.1.id', 'memulai')
                ->has('html'));
    }

    public function test_chapter_headings_have_anchor_ids(): void
    {
        $html = UserGuide::render()['html'];

        $this->assertStringContainsString('<h2 id="integrasi-dasbor-portofolio">', $html);
        $this->assertStringContainsString('src="/images/panduan/01-login.webp"', $html);
    }
}
