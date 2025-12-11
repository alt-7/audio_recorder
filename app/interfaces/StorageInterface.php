<?php

declare(strict_types=1);

namespace app\interfaces;

interface StorageInterface
{
    public function saveTempFile(string $base64String): string;

    public function getRootPath(): string;

    public function getRelativeUrl(string $fullPath): string;

    public function prepareTargetFile(string $department, string $operator): string;
}