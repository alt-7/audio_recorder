<?php

declare(strict_types=1);

namespace app\services;

use app\dto\StartRecordingDto;
use app\dto\StopRecordingDto;
use app\factories\RecordingFactory;
use app\interfaces\AudioProcessorInterface;
use app\interfaces\RecordingRepositoryInterface;
use app\interfaces\StorageInterface;
use app\exceptions\AudioException;
use app\models\Recording;
use Throwable;
use Yii;

readonly class RecordingService
{
    public function __construct(private RecordingFactory $factory, private RecordingRepositoryInterface $repository, private StorageInterface $storage, private AudioProcessorInterface $processor)
    {
    }

    public function start(StartRecordingDto $dto): string
    {
        $recording = $this->factory->create($dto);
        $this->repository->save($recording);

        return $recording->session_id;
    }

    /**
     * @throws AudioException
     * @throws Throwable
     */
    public function stop(StopRecordingDto $dto): array
    {
        $recording = $this->repository->findBySessionId($dto->sessionId);
        if (!$recording) {
            throw new AudioException("Сессия записи не найдена: {$dto->sessionId}");
        }

        if ($recording->status !== Recording::STATUS_RECORDING) {
            throw new AudioException("Запись уже обработана или обрабатывается.");
        }

        $recording->status = Recording::STATUS_PROCESSING;
        $this->repository->save($recording);

        $tempFilePath = null;
        try {
            $tempFilePath = $this->storage->saveTempFile($dto->audioData);
            $targetFilePath = $this->storage->prepareTargetFile($recording->department, $recording->operator_name);
            $meta = $this->processor->process($tempFilePath, $targetFilePath);

            $recording->file_path = $this->storage->getRelativeUrl($targetFilePath);
            $recording->file_size = $meta['file_size'];
            $recording->duration = $meta['duration'];
            $recording->status = Recording::STATUS_COMPLETED;

            $this->repository->save($recording);

            return $recording->toArray();

        } catch (Throwable $e) {
            $recording->status = Recording::STATUS_ERROR;
            $this->repository->save($recording);

            Yii::error("Обработка завершилась неудачей: " . $e->getMessage(), 'audio_service');
            throw new AudioException($e->getMessage(), (int)$e->getCode(), $e);
        } finally {
            if ($tempFilePath && file_exists($tempFilePath)) {
                @unlink($tempFilePath);
            }
        }
    }
}