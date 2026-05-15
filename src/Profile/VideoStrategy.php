<?php

declare(strict_types=1);

namespace KraenzleRitter\AiCataloging\Profile;

/**
 * Strategy for handling video inputs.
 *
 *  - `Direct`: upload the video derivative to a multimodal provider
 *    that accepts raw video (Gemini File API). Lower latency, higher
 *    accuracy on visual cues, bounded by provider's per-file limits.
 *  - `Frames`: extract keyframes with FFmpeg (scene detection or
 *    fixed interval) + transcribe the audio track via Whisper-STT,
 *    feed both to the agent. Works with text-only providers, no
 *    upstream file-size limit, slower.
 */
enum VideoStrategy: string
{
    case Direct = 'direct';
    case Frames = 'frames';
}
