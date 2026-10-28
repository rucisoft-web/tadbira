<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Panduan pengguna dari resources/docs/panduan.md: HTML siap tampil dan daftar bab untuk submenu.
 */
class UserGuide
{
    public static function path(): string
    {
        return resource_path('docs/panduan.md');
    }

    /** @return array{html: string, sections: array<int, array{id: string, title: string}>} */
    public static function render(): array
    {
        $path = static::path();

        return Cache::rememberForever('panduan:'.filemtime($path), function () use ($path) {
            $html = Str::markdown(file_get_contents($path), ['html_input' => 'strip', 'allow_unsafe_links' => false]);
            $sections = [];
            $used = [];

            $html = preg_replace_callback('/<h([23])>(.*?)<\/h\1>/s', function ($m) use (&$sections, &$used) {
                $title = html_entity_decode(strip_tags($m[2]));
                $base = Str::slug(preg_replace('/^\d+\.\s*/', '', $title));
                $used[$base] = ($used[$base] ?? 0) + 1;
                $id = $used[$base] > 1 ? $base.'-'.$used[$base] : $base;

                if ($m[1] === '2') {
                    $sections[] = ['id' => $id, 'title' => $title];
                }

                return "<h{$m[1]} id=\"{$id}\">{$m[2]}</h{$m[1]}>";
            }, $html);

            // Gambar dimuat malas; ukurannya ditulis agar tata letak tidak bergeser saat dimuat.
            $html = preg_replace_callback('/<img src="(\/images\/[^"]+)"/', function ($m) {
                $size = @getimagesize(public_path($m[1]));

                return $size ? "<img loading=\"lazy\" width=\"{$size[0]}\" height=\"{$size[1]}\" src=\"{$m[1]}\"" : $m[0];
            }, $html);

            return ['html' => $html, 'sections' => $sections];
        });
    }

    /** @return array<int, array{id: string, title: string}> */
    public static function sections(): array
    {
        return static::render()['sections'];
    }
}
