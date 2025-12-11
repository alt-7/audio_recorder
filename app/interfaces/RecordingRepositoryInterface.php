<?php

declare(strict_types=1);

namespace app\interfaces;

use app\models\Recording;

interface RecordingRepositoryInterface
{
    public function save(Recording $recording): void;
    public function findBySessionId(string $sessionId): ?Recording;
}