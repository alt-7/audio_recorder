<?php

declare(strict_types=1);

namespace tests\api;

use ApiTester;
use Codeception\Util\HttpCode;
use Exception;
use tests\unit\fixtures\UserFixture;

class RecordingCest
{
    private string $sessionId;
    private string $apiKey = 'secret-api-key-123';

    public function _fixtures(): array
    {
        return [
            'users' => [
                'class'    => UserFixture::class,
                'dataFile' => '@app/tests/unit/fixtures/data/users.php',
            ],
        ];
    }


    public function tryUnauthorizedAccess(ApiTester $I): void
    {
        $I->wantTo('Check auth protection');
        $I->sendPOST('/api/recording/start', ['department' => 'sales', 'operator_name' => 'tester']);
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    /**
     * @throws Exception
     */
    public function tryStartRecording(ApiTester $I): void
    {
        $I->wantTo('Start recording with valid key');

        $I->haveHttpHeader('X-Api-Key', $this->apiKey);
        $I->sendPOST('/api/recording/start', [
            'department'    => 'sales',
            'operator_name' => 'tester_integ'
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['status' => 'success']);

        $this->sessionId = $I->grabDataFromResponseByJsonPath('$.session_id')[0];
    }

    public function tryValidationFail(ApiTester $I): void
    {
        $I->wantTo('Check validation errors');

        $I->haveHttpHeader('X-Api-Key', $this->apiKey);
        $I->sendPOST('/api/recording/start', []);

        $I->seeResponseContainsJson([
            'status'  => 'error',
            'message' => 'Ошибка валидации данных'
        ]);
    }
}