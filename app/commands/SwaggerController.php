<?php

declare(strict_types=1);

namespace app\commands;

use Yii;
use yii\console\Controller;
use OpenApi\Generator;
use yii\console\ExitCode;

class SwaggerController extends Controller
{
    /**
     * Генерирует файл openapi.json в папке web
     * Запуск: php yii swagger/generate
     */
    public function actionGenerate(): int
    {
        $this->stdout("Сканирование атрибутов OpenAPI...\n");

        $openapi = Generator::scan([
            Yii::getAlias('@app/app/controllers'),
            Yii::getAlias('@app/app/dto'),
            Yii::getAlias('@app/app/docs'),
        ]);

        $path = Yii::getAlias('@app/web/openapi.json');
        if (file_put_contents($path, $openapi->toJson()) === false) {
            $this->stderr("Ошибка записи файла!\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Документация сохранена: $path\n");
        return ExitCode::OK;
    }
}