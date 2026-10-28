<?php

namespace Tests\Feature;

use App\Support\UserGuide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuideTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_executive_page_is_public(): void
    {
        $this->get('/untuk-eksekutif')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Executive')->has('demos')->has('guide')->has('links'));
    }

    public function test_workshop_page_is_public(): void
    {
        $this->get('/workshop')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Workshop')->has('demos')->has('workshop')->has('links'));
    }

    public function test_workshop_materials_list_every_session(): void
    {
        $this->get('/workshop/materi')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('WorkshopMaterials')->has('sessions', 13));
    }

    public function test_workshop_session_page_and_placeholders(): void
    {
        $this->get('/workshop/materi/sesi-4')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('WorkshopMaterial')
                ->where('session.title', 'Menyusun SRK dan jadwal')
                ->where('session.videoId', null)
                ->where('session.images.0.src', '/images/workshop/sesi-04-a.webp')
                ->where('session.prev.slug', 'sesi-3')
                ->where('session.next.slug', 'sesi-5'));

        $this->get('/workshop/materi/penutupan')
            ->assertInertia(fn ($page) => $page
                ->where('session.images.0.src', null)
                ->where('session.images.0.path', 'images/workshop/penutupan-a.webp'));

        $this->get('/workshop/materi/tidak-ada')->assertNotFound();
    }

    public function test_youtube_links_become_video_ids(): void
    {
        foreach ([
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ?si=abc',
            'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'https://www.youtube.com/watch?feature=share&v=dQw4w9WgXcQ',
            'dQw4w9WgXcQ',
        ] as $url) {
            $this->assertSame('dQw4w9WgXcQ', \App\Support\WorkshopMaterial::youtubeId($url), $url);
        }

        $this->assertNull(\App\Support\WorkshopMaterial::youtubeId(''));
        $this->assertNull(\App\Support\WorkshopMaterial::youtubeId('https://vimeo.com/123'));
    }
}
