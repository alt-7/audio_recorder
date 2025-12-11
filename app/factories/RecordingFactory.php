<?php

declare(strict_types=1);

namespace app\factories;

use app\dto\StartRecordingDto;
use app\models\Recording;
use Ramsey\Uuid\Uuid;

class RecordingFactory
{
    public function create(StartRecordingDto $dto): Recording
    {
        $recording = new Recording();
        $recording->session_id = Uuid::uuid4()->toString();
        $recording->department = $dto->department;
        $recording->operator_name = $dto->operatorName;
        $recording->status = Recording::STATUS_RECORDING;

        return $recording;
    }
}