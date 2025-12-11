<?php
declare(strict_types=1);

namespace app\exceptions;

use Throwable;
use yii\web\UnprocessableEntityHttpException;

class ValidationException extends UnprocessableEntityHttpException
{
    public array $errors;

    public function __construct(array $errors, string $message = 'Validation failed', int $code = 0, Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    public function getName(): string
    {
        return 'Ошибка проверки данных';
    }
}