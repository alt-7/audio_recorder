<?php

declare(strict_types=1);

namespace tests\unit\services;

use app\dto\StartRecordingDto;
use app\dto\StopRecordingDto;
use app\exceptions\AudioException;
use app\interfaces\AudioProcessorInterface;
use app\interfaces\RecordingRepositoryInterface;
use app\interfaces\StorageInterface;
use app\models\Recording;
use app\services\RecordingService;
use Codeception\Test\Unit;
use Exception;
use Throwable;

class RecordingServiceTest extends Unit
{
    /**
     * @throws Exception
     */
    public function testStartRecordingCreatesSession(): void
    {
        $repoMock = $this->makeEmpty(RecordingRepositoryInterface::class, [
            'save' => function (Recording $model) {
                verify($model->status)->equals(Recording::STATUS_RECORDING);
                return true;
            }
        ]);
        $storageMock = $this->makeEmpty(StorageInterface::class);
        $processorMock = $this->makeEmpty(AudioProcessorInterface::class);

        $service = new RecordingService($repoMock, $storageMock, $processorMock);
        $dto = new StartRecordingDto('sales', 'tester');

        $sessionId = $service->start($dto);
        verify($sessionId)->notEmpty();
    }

    /**
     * @throws AudioException
     * @throws Throwable
     */
    public function testStopRecordingProcessesFile(): void
    {
        $sessionId = 'test-uuid';
        $fakeWebUrl = '/audio-records/sales/tester/final.mp3';

        $recordingModel = new Recording(['session_id' => $sessionId, 'status' => 'recording']);

        $repoMock = $this->makeEmpty(RecordingRepositoryInterface::class, [
            'findBySessionId' => fn($id) => $recordingModel,
            'save'            => function (Recording $model) {
                return true;
            }
        ]);

        $storageMock = $this->makeEmpty(StorageInterface::class, [
            'saveTempFile'      => fn($data) => '/tmp/fake.webm',
            'prepareTargetFile' => fn($d, $o) => '/full/path/final.mp3',
            'getRelativeUrl'    => fn($p) => $fakeWebUrl
        ]);

        $processorMock = $this->makeEmpty(AudioProcessorInterface::class, [
            'process' => fn($in, $out) => ['duration' => 120.5, 'file_size' => 102400]
        ]);

        $service = new RecordingService($repoMock, $storageMock, $processorMock);
        $dto = new StopRecordingDto($sessionId, 'sales', 'tester', 'base64_data');

        $result = $service->stop($dto);

        verify($result['status'])->equals(Recording::STATUS_COMPLETED);
    }

    /**
     * @throws Throwable
     */
    public function testStopThrowsExceptionIfSessionNotFound(): void
    {
        $repoMock = $this->makeEmpty(RecordingRepositoryInterface::class, [
            'findBySessionId' => fn($id) => null
        ]);

        $storageMock = $this->makeEmpty(StorageInterface::class);
        $processorMock = $this->makeEmpty(AudioProcessorInterface::class);

        $service = new RecordingService($repoMock, $storageMock, $processorMock);
        $dto = new StopRecordingDto('wrong-id', 'sales', 'tester', 'data');
        $this->expectException(AudioException::class);
        $this->expectExceptionMessage('Сессия записи не найдена');

        $service->stop($dto);
    }

    /**
     * @throws Throwable
     */
    public function testStopHandlesStorageErrorAndSetsStatusError(): void
    {
        $sessionId = 'test-uuid';
        $recordingModel = new Recording(['session_id' => $sessionId, 'status' => 'recording']);

        $repoMock = $this->makeEmpty(RecordingRepositoryInterface::class, [
            'findBySessionId' => fn($id) => $recordingModel,
            'save'            => function (Recording $model) {
                return true;
            }
        ]);

        $storageMock = $this->makeEmpty(StorageInterface::class, [
            'saveTempFile' => function ($data) {
                throw new AudioException("Неверный формат данных (Base64)");
            }
        ]);

        $processorMock = $this->makeEmpty(AudioProcessorInterface::class);
        $service = new RecordingService($repoMock, $storageMock, $processorMock);
        $dto = new StopRecordingDto($sessionId, 'sales', 'tester', 'bad_data');

        $this->expectException(AudioException::class);
        $this->expectExceptionMessage("Неверный формат данных (Base64)");

        try {
            $service->stop($dto);
        } catch (AudioException $e) {
            verify($recordingModel->status)->equals(Recording::STATUS_ERROR);
            throw $e;
        }
    }
}