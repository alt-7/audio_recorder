<?php

declare(strict_types=1);

namespace app\filters;

use Yii;
use yii\base\Action;
use yii\base\ActionFilter;
use yii\base\InvalidConfigException;
use yii\caching\CacheInterface;
use yii\di\Instance;
use yii\web\TooManyRequestsHttpException;

class IpRateLimitFilter extends ActionFilter
{
    private const HEADER_LIMIT = 'X-Rate-Limit-Limit';
    private const HEADER_REMAINING = 'X-Rate-Limit-Remaining';

    public int $maxRequests = 10;
    public int $period = 60;

    public string|array|CacheInterface $cache = 'cache';

    private CacheInterface $cacheComponent;

    /**
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this->cacheComponent = Instance::ensure($this->cache, CacheInterface::class);
    }

    /**
     * @param Action $action
     * @throws TooManyRequestsHttpException
     */
    public function beforeAction($action): bool
    {
        $ip = Yii::$app->request->userIP;
        if (!$ip) {
            return true;
        }

        $key = $this->generateCacheKey($ip);
        $current = (int)$this->cacheComponent->get($key);

        if ($current >= $this->maxRequests) {
            $this->addHeaders($this->maxRequests, 0);
            throw new TooManyRequestsHttpException('Превышен лимит запросов. Попробуйте позже.');
        }

        $this->incrementCounter($key, $current);
        $this->addHeaders($this->maxRequests, $this->maxRequests - ($current + 1));

        return parent::beforeAction($action);
    }

    private function incrementCounter(string $key, int $current): void
    {
        $this->cacheComponent->set($key, $current + 1, $this->period);
    }

    private function addHeaders(int $limit, int $remaining): void
    {
        $headers = Yii::$app->response->headers;
        $headers->set(self::HEADER_LIMIT, $limit);
        $headers->set(self::HEADER_REMAINING, max(0, $remaining));
    }

    private function generateCacheKey(string $ip): string
    {
        return "rate_limit:{$ip}";
    }
}