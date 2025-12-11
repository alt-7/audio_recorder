<?php

declare(strict_types=1);

namespace app\dto;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "Start Recording Request",
    description: "Данные для начала записи",
    required: ["department", "operator_name"]
)]
readonly class StartRecordingDto
{
    public function __construct(
        #[OA\Property(property: "department", description: "Название отдела", example: "sales")]
        public string $department,

        #[OA\Property(property: "operator_name", description: "Имя оператора", example: "ivanov_ivan")]
        public string $operatorName
    )
    {
    }
}