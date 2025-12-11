<?php

declare(strict_types=1);

namespace app\repositories;

use app\interfaces\RecordingRepositoryInterface;
use app\models\Recording;
use app\exceptions\RecordingSaveException;
use yii\db\Exception;

class RecordingRepository implements RecordingRepositoryInterface
{
    /**
     * @throws RecordingSaveException
     * @throws Exception
     */
    public function save(Recording $recording): void
    {
        if (!$recording->save()) {
            throw new RecordingSaveException($recording->errors);
        }
    }

    public function findBySessionId(string $sessionId): ?Recording
    {
        return Recording::findOne(['session_id' => $sessionId]);
    }
}