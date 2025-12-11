<?php

declare(strict_types=1);

namespace app\controllers;

use app\dto\StartRecordingDto;
use app\dto\StopRecordingDto;
use app\exceptions\AudioException;
use app\exceptions\ValidationException;
use app\filters\IpRateLimitFilter;
use app\services\RecordingService;
use app\validators\StartRecordingValidator;
use app\validators\StopRecordingValidator;
use Throwable;
use Yii;
use yii\filters\auth\HttpHeaderAuth;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\rest\Controller;
use yii\web\Response;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Recording", description: "API Записи звука")]
class AudioRecordController extends Controller
{
    public function __construct($id, $module, private readonly RecordingService $recordingService, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        unset($behaviors['authenticator']);

        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors'  => [
                'Origin'                           => ['*'],
                'Access-Control-Request-Method'    => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers'   => ['*'],
                'Access-Control-Allow-Credentials' => null,
                'Access-Control-Max-Age'           => 86400,
                'Access-Control-Expose-Headers'    => [],
            ],
        ];

        $behaviors['contentNegotiator'] = [
            'class'   => ContentNegotiator::class,
            'formats' => ['application/json' => Response::FORMAT_JSON],
        ];

        $behaviors['authenticator'] = [
            'class'  => HttpHeaderAuth::class,
            'header' => 'X-Api-Key',
            'except' => ['options'],
        ];

        $isTest = (defined('YII_ENV') && YII_ENV === 'test')
            || Yii::$app->request->headers->get('X-Test-Mode') === 'true';
        $behaviors['rateLimiter'] = [
            'class'       => IpRateLimitFilter::class,
            'maxRequests' => $isTest ? 1000 : 10,
            'period'      => 60,
        ];

        return $behaviors;
    }

    /**
     * @throws ValidationException
     */
    #[OA\Post(
        path: '/api/recording/start',
        summary: 'Начать запись',
        security: [['ApiKeyAuth' => []]],
        tags: ['Recording']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/StartRecordingDto')
    )]
    #[OA\Response(
        response: 200,
        description: 'Успешный старт',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'session_id', type: 'string', example: 'uuid-session-id'),
                new OA\Property(property: 'message', type: 'string', example: 'Запись начата')
            ]
        )
    )]
    #[OA\Response(ref: '#/components/responses/Unauthorized', response: 401)]
    #[OA\Response(ref: '#/components/responses/ValidationError', response: 422)]
    #[OA\Response(ref: '#/components/responses/DefaultError', response: 500)]
    public function actionStart(): array
    {
        $data = Yii::$app->request->post();

        $model = new StartRecordingValidator();
        if (!$model->load($data, '') || !$model->validate()) {
            throw new ValidationException($model->errors);
        }

        $dto = new StartRecordingDto($model->department, $model->operator_name);
        $sessionId = $this->recordingService->start($dto);

        return [
            'session_id' => $sessionId,
            'message'    => 'Запись начата'
        ];
    }

    #[OA\Post(
        path: '/api/recording/stop',
        summary: 'Остановить запись',
        security: [['ApiKeyAuth' => []]],
        tags: ['Recording']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/StopRecordingDto')
    )]
    #[OA\Response(
        response: 200,
        description: 'Запись сохранена',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'file_path', type: 'string', example: '/audio-records/sales/ivanov_ivan/2024-12-08/143022_ivanov_ivan.mp3'),
                new OA\Property(property: 'file_size', type: 'integer', example: 245678),
                new OA\Property(property: 'duration', type: 'number', format: 'float', example: 125.5),
                new OA\Property(property: 'message', type: 'string', example: 'Запись сохранена')
            ]
        )
    )]
    #[OA\Response(ref: '#/components/responses/Unauthorized', response: 401)]
    #[OA\Response(ref: '#/components/responses/ValidationError', response: 422)]
    #[OA\Response(ref: '#/components/responses/DefaultError', response: 500)]
    /**
     * @throws AudioException
     * @throws ValidationException
     * @throws Throwable
     */
    public function actionStop(): array
    {
        $data = Yii::$app->request->post();

        $model = new StopRecordingValidator();
        if (!$model->load($data, '') || !$model->validate()) {
            throw new ValidationException($model->errors);
        }

        $dto = new StopRecordingDto($model->session_id, $model->department, $model->operator_name, $model->audio_data);
        $result = $this->recordingService->stop($dto);

        return [
            'file_path' => $result['file_path'],
            'file_size' => $result['file_size'],
            'duration'  => $result['duration'],
            'message'   => 'Запись сохранена'
        ];
    }
}