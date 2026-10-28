<?php

namespace App\Support;

/**
 * Materi instruktur workshop dari resources/workshop/materi.php: gambar dicari di
 * images/workshop/ folder web (public_path) dan tautan YouTube diubah menjadi ID video untuk disematkan.
 */
class WorkshopMaterial
{
    private const IMAGE_DIR = 'images/workshop';

    private const IMAGE_EXTENSIONS = ['webp', 'jpg', 'jpeg', 'png'];

    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        return require resource_path('workshop/materi.php');
    }

    /** Ringkasan semua sesi untuk daftar dan navigasi samping. */
    public static function outline(): array
    {
        return array_map(fn (array $s) => [
            'slug' => $s['slug'],
            'day' => $s['day'],
            'time' => $s['time'],
            'minutes' => $s['minutes'],
            'tag' => $s['tag'],
            'title' => $s['title'],
            'summary' => $s['summary'],
            'hasVideo' => static::youtubeId($s['video'] ?? '') !== null,
            'images' => count($s['images'] ?? []),
            'imagesReady' => count(array_filter(array_map(fn ($i) => static::imageSrc($i['file']), $s['images'] ?? []))),
        ], static::all());
    }

    public static function find(string $slug): ?array
    {
        $sessions = static::all();
        $index = array_search($slug, array_column($sessions, 'slug'), true);

        if ($index === false) {
            return null;
        }

        $session = $sessions[$index];
        $session['videoId'] = static::youtubeId($session['video'] ?? '');
        $session['images'] = array_map(fn (array $image) => $image + [
            'src' => static::imageSrc($image['file']),
            'path' => self::IMAGE_DIR.'/'.$image['file'].'.webp',
        ], $session['images'] ?? []);
        $session['prev'] = $index > 0 ? array_intersect_key($sessions[$index - 1], array_flip(['slug', 'title'])) : null;
        $session['next'] = $index < count($sessions) - 1 ? array_intersect_key($sessions[$index + 1], array_flip(['slug', 'title'])) : null;

        return $session;
    }

    public static function imageSrc(string $file): ?string
    {
        foreach (self::IMAGE_EXTENSIONS as $ext) {
            $relative = self::IMAGE_DIR.'/'.$file.'.'.$ext;
            if (is_file(public_path($relative))) {
                return '/'.$relative;
            }
        }

        return null;
    }

    /** Menerima tautan watch?v=, youtu.be/, embed/, shorts/, atau ID 11 karakter. */
    public static function youtubeId(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (preg_match('~^[A-Za-z0-9_-]{11}$~', $url)) {
            return $url;
        }

        if (preg_match('~(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return $m[1];
        }

        return null;
    }
}
