<?php

declare(strict_types=1);

namespace app\interfaces;

interface AudioProcessorInterface
{
    public function process(string $inputPath, string $outputPath): array;
}