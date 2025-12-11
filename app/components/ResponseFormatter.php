<?php

declare(strict_types=1);

namespace app\components;

use app\exceptions\AudioException;
use app\exceptions\ValidationException;
use Yii;
use yii\web\HttpException;
use yii\web\Response;
use Throwable;

class ResponseFormatter
{
    private const STATUS_SUCCESS = 'success';
    private const STATUS_ERROR = 'error';
    private const MSG_DEFAULT_ERROR = 'Произошла ошибка';
    private const MSG_VALIDATION_ERROR = 'Ошибка валидации данных';
    private const MSG_INTERNAL_ERROR = 'Internal Server Error';

    public static function format(Response $response): array
    {
        $data = $response->data;

        if ($response->isSuccessful) {
            return self::success($data);
        }

        return self::error($data, $response->statusCode);
    }

    private static function success(mixed $data): array
    {
        $payload = is_array($data) ? $data : ['data' => $data];
        return array_merge(['status' => self::STATUS_SUCCESS], $payload);
    }

    private static function error(mixed $data, int $statusCode): array
    {
        $exception = Yii::$app->errorHandler->exception;

        [$message, $errors] = self::resolveMessageAndErrors($exception, $data, $statusCode);

        $result = [
            'status'  => self::STATUS_ERROR,
            'message' => $message,
        ];

        if ($errors !== null) {
            $result['errors'] = $errors;
        }

        if (YII_DEBUG) {
            $debugInfo = self::resolveDebugInfo($data);
            if (!empty($debugInfo)) {
                $result['debug'] = $debugInfo;
            }
        }

        return $result;
    }

    private static function resolveMessageAndErrors(?Throwable $exception, mixed $data, int $statusCode): array
    {
        $message = $data['message'] ?? self::MSG_DEFAULT_ERROR;
        $errors = null;

        if ($exception instanceof ValidationException) {
            $firstFieldErrors = reset($exception->errors);
            $firstErrorMessage = is_array($firstFieldErrors) ? ($firstFieldErrors[0] ?? null) : null;
            $finalMessage = $firstErrorMessage ?? self::MSG_VALIDATION_ERROR;

            return [$finalMessage, null];
        }

        if ($exception instanceof AudioException || $exception instanceof HttpException) {
            return [$exception->getMessage(), null];
        }

        if ($statusCode >= 500 && !YII_DEBUG) {
            return [self::MSG_INTERNAL_ERROR, null];
        }

        return [$message, $errors];
    }

    private static function resolveDebugInfo(mixed $data): array
    {
        if (is_array($data) && isset($data['stack-trace'])) {
            return [
                'type' => $data['type'] ?? 'Unknown',
                'file' => $data['file'] ?? '',
                'line' => $data['line'] ?? '',
            ];
        }
        return [];
    }
}