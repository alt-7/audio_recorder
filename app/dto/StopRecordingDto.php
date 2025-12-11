<?php

declare(strict_types=1);

namespace app\dto;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "Stop Recording Request",
    description: "Данные для завершения записи",
    required: ["session_id", "department", "operator_name", "audio_data"]
)]
readonly class StopRecordingDto
{
    public function __construct(
        #[OA\Property(property: "session_id", description: "ID сессии", example: "uuid-session-id")]
        public string $sessionId,

        #[OA\Property(property: "department", description: "Название отдела", example: "sales")]
        public string $department,

        #[OA\Property(property: "operator_name", description: "Имя оператора", example: "ivanov_ivan")]
        public string $operatorName,

        #[OA\Property(property: "audio_data", description: "Аудиофайл в формате Base64", example: "base64_encoded_audio")]
        public string $audioData
    )
    {
    }
}