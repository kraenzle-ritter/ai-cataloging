<?php

declare(strict_types=1);

namespace KraenzleRitter\AiCataloging\Modality;

use Laravel\Ai\Files\Image;
use Laravel\Ai\Files\LocalImage;

/**
 * Builds image-typed agent attachments from local file paths.
 *
 * The simplest of the modality strategies — no FFmpeg, no STT, no
 * file-API upload. Each path becomes a `Laravel\Ai\Files\LocalImage`
 * the SDK ships verbatim to the provider.
 *
 * Caller is responsible for the file existing and being readable —
 * the package doesn't probe disk to keep it framework-agnostic.
 */
final class ImageStrategy
{
    /**
     * @param  list<string>           $paths       absolute file paths
     * @return list<LocalImage>
     */
    public function buildAttachments(array $paths): array
    {
        $out = [];
        foreach ($paths as $path) {
            if ($path === '') {
                continue;
            }
            $out[] = Image::fromPath($path);
        }

        return $out;
    }
}
