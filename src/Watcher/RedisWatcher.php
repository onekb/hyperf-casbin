<?php

declare(strict_types=1);

namespace Donjan\Casbin\Watcher;

use Hyperf\Redis\Redis;
use Casbin\Persist\Watcher;
use Closure;

class RedisWatcher implements Watcher
{
    private Closure $callback;

    private Redis $pubRedis;

    private Redis $subRedis;

    private string $channel;

    public function __construct()
    {
        $this->pubRedis = $this->createRedisClient();
        $this->subRedis = $this->createRedisClient();
        $this->channel = '/casbin2';
    }

    public function subscribe()
    {
        do {
            try {
                $this->subRedis->subscribe([$this->channel], function ($channel, $message) {
                    if ($this->callback) {
                        call_user_func($this->callback);
                    }
                });
            } catch (\Exception $e) {
            }
        } while (true);
    }

    /**
     * Sets the callback function that the watcher will call when the policy in DB has been changed by other instances.
     * A classic callback is loadPolicy() method of Enforcer class.
     *
     * @param Closure $func
     */
    public function setUpdateCallback(Closure $func): void
    {
        $this->callback = $func;
    }

    /**
     * Update calls the update callback of other instances to synchronize their policy.
     * It is usually called after changing the policy in DB, like savePolicy() method of Enforcer class,
     * addPolicy(), removePolicy(), etc.
     */
    public function update(): void
    {
        $this->pubRedis->publish($this->channel, 'casbin rules updated');
    }

    /**
     * Close stops and releases the watcher, the callback function will not be called any more.
     */
    public function close(): void
    {
        $this->pubRedis->close();
        $this->subRedis->close();
    }

    /**
     * Create redis client
     */
    private function createRedisClient(): Redis
    {
        /** @var Redis */
//        return (\Hyperf\Utils\ApplicationContext::getContainer())->get(\Hyperf\Redis\Redis::class);
        return make(Redis::class);
    }
}