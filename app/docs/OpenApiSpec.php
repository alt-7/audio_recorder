<?php

declare(strict_types=1);

namespace app\docs;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'API сервиса записи звука.',
    title: 'Audio Recorder API'
)]
#[OA\Server(
    url: '/',
    description: 'Local Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'ApiKeyAuth',
    type: 'apiKey',
    name: 'X-Api-Key',
    in: 'header'
)]
#[OA\Response(
    response: 'DefaultError',
    description: 'Общая ошибка',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'status', type: 'string', example: 'error'),
            new OA\Property(property: 'message', type: 'string', example: 'Описание ошибки')
        ]
    )
)]
#[OA\Response(
    response: 'Unauthorized',
    description: 'Ошибка доступа',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'status', type: 'string', example: 'error'),
            new OA\Property(property: 'message', type: 'string', example: 'Your request was made with invalid credentials.')
        ]
    )
)]
#[OA\Response(
    response: 'ValidationError',
    description: 'Ошибка валидации полей',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'status', type: 'string', example: 'error'),
            new OA\Property(property: 'message', type: 'string', example: 'Ошибка валидации данных'),
        ]
    )
)]
class OpenApiSpec
{
}