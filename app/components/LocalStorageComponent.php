<?php

declare(strict_types=1);

namespace app\components;

use Random\RandomException;
use Yii;
use yii\base\Component;
use app\interfaces\StorageInterface;
use app\exceptions\AudioException;

class LocalStorageComponent extends Component implements StorageInterface
{
    public string $storagePath = '@webroot/audio-records';

    /**
     * @throws AudioException
     */
    public function init(): void
    {
        parent::init();
        $this->storagePath = Yii::getAlias($this->storagePath);

        $this->ensureDirectoryExists($this->storagePath);
    }

    /**
     * @throws AudioException
     * @throws RandomException
     */
    public function saveTempFile(string $base64String): string
    {
        if (!preg_match('/^data:audio\/(\w+);base64,/', $base64String, $matches)) {
            throw new AudioException("Неверный формат данных (ожидается base64 audio)");
        }

        $extension = strtolower($matches[1]);
        $allowed = ['webm', 'ogg', 'wav', 'mp3', 'mpeg'];

        if (!in_array($extension, $allowed)) {
            throw new AudioException("Формат аудио '{$extension}' не поддерживается. Разрешены: " . implode(', ', $allowed));
        }

        $binaryData = $this->decodeBase64($base64String);
        $filename = 'raw_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;

        if (file_put_contents($tempPath, $binaryData) === false) {
            throw new AudioException("Не удалось записать временный файл");
        }

        return $tempPath;
    }

    public function getRootPath(): string
    {
        return $this->storagePath;
    }

    public function getRelativeUrl(string $fullPath): string
    {
        $webRoot = Yii::getAlias('@webroot');
        return str_replace($webRoot, '', $fullPath);
    }

    /**
     * @throws AudioException
     */
    public function prepareTargetFile(string $department, string $operator): string
    {
        $safeDept = preg_replace('/[^a-zA-Z0-9_]/', '', $department);
        $safeOper = preg_replace('/[^a-zA-Z0-9_]/', '', $operator);
        $date = date('Y-m-d');

        $targetDir = sprintf('%s/%s/%s/%s', $this->storagePath, $safeDept, $safeOper, $date);

        $this->ensureDirectoryExists($targetDir);

        $timestamp = date('His');
        return sprintf('%s/%s_%s.mp3', $targetDir, $timestamp, $safeOper);
    }

    /**
     * @throws AudioException
     */
    private function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
            throw new AudioException("Не удалось создать директорию: $path");
        }
    }

    /**
     * @throws AudioException
     */
    private function decodeBase64(string $input): string
    {
        if (str_contains($input, ',')) {
            $input = explode(',', $input)[1];
        }

        $input = preg_replace('/\s+/', '', $input);
        $decoded = base64_decode($input, true);
        if ($decoded === false) {
            throw new AudioException("Некорректные данные Base64");
        }

        return $decoded;
    }
}