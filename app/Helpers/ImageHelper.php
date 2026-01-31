<?php

namespace App\Helpers;

class ImageHelper
{
    public static function extractImages(string $body): array
    {
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/', $body, $matches);
        return $matches[1] ?? [];
    }
}
