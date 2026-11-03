<?php

namespace App\Support;

class InformationVideo
{
    public static function embedUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $path = trim($parts['path'] ?? '', '/');
        parse_str($parts['query'] ?? '', $query);

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com', 'youtu.be'], true)) {
            $videoId = $host === 'youtu.be'
                ? explode('/', $path)[0]
                : ($query['v'] ?? (preg_match('~^(?:embed|shorts|live)/([^/]+)~', $path, $matches) ? $matches[1] : null));

            return is_string($videoId) && preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId)
                ? 'https://www.youtube-nocookie.com/embed/'.$videoId
                : null;
        }

        if (in_array($host, ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true)) {
            $videoId = preg_match('/(?:^|\/)(\d+)(?:\/|$)/', $path, $matches) ? $matches[1] : null;

            return $videoId ? 'https://player.vimeo.com/video/'.$videoId : null;
        }

        return null;
    }
}
