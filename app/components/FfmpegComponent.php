<?php

declare(strict_types=1);

namespace app\components;

use yii\base\Component;
use app\interfaces\AudioProcessorInterface;
use app\exceptions\AudioException;

class FfmpegComponent extends Component implements AudioProcessorInterface
{
    public string $ffmpegBinary = 'ffmpeg';
    public string $ffprobeBinary = 'ffprobe';

    /**
     * 1. highpass=f=300      -> Убираем гул
     * 2. afftdn=nr=20        -> Давим шум
     * 3. silenceremove:
     * - start_periods=1
     * - start_threshold=-35dB -> Режем всё, что тише -35dB (щелчки, шум)
     * - stop_periods=1
     * - stop_duration=0.5     -> Ждем 0.5 секунду тишины
     * - stop_threshold=-55dB  -> ПРежем всё, что тише 55dB (щелчки, шум)
     * * 4. loudnorm           -> Нормализация
     */
    public string $audioFilters = 'highpass=f=300,afftdn=nr=20,silenceremove=start_periods=1:start_duration=0.1:start_threshold=-35dB:stop_periods=1:stop_duration=0.5:stop_threshold=-55dB:detection=peak,loudnorm=I=-16:TP=-1.5:LRA=11';

    /**
     * @throws AudioException
     */
    public function process(string $inputPath, string $outputPath): array
    {
        $command = $this->buildCommand($inputPath, $outputPath);

        $output = [];
        $returnCode = 0;

        // 2>&1 перенаправляет ошибки в стандартный вывод, чтобы могли их прочитать
        exec($command . ' 2>&1', $output, $returnCode);
        if ($returnCode !== 0) {
            throw new AudioException("Ошибка FFmpeg: " . implode("\n", $output));
        }

        if (file_exists($outputPath) && filesize($outputPath) === 0) {
            return [
                'duration'  => 0.0,
                'file_size' => 0
            ];
        }

        return [
            'duration'  => $this->getAudioDuration($outputPath),
            'file_size' => filesize($outputPath)
        ];
    }

    private function buildCommand(string $input, string $output): string
    {
        return sprintf(
            '%s -y -i %s -af "%s" -c:a libmp3lame -q:a 2 -ar 44100 %s',
            $this->ffmpegBinary,
            escapeshellarg($input),
            $this->audioFilters,
            escapeshellarg($output)
        );
    }

    /**
     * @throws AudioException
     */
    private function getAudioDuration(string $filePath): float
    {
        $cmd = sprintf(
            '%s -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 %s',
            $this->ffprobeBinary,
            escapeshellarg($filePath)
        );

        $output = [];
        $returnCode = 0;

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0 || empty($output)) {
            if (file_exists($filePath)) {
                return 0.0;
            }
            throw new AudioException("Не удалось получить длительность файла: $filePath");
        }

        return (float)$output[0];
    }
}