<?php

declare(strict_types=1);

namespace Donjan\Casbin\Process;

use Donjan\Casbin\Watcher\RedisWatcher;
use Hyperf\Process\AbstractProcess;
use Hyperf\Di\Annotation\Inject;

class WatcherProcess extends AbstractProcess
{
    /**
     * @Inject
     * @var RedisWatcher
     */
    private RedisWatcher $watcher;

    public function handle(): void
    {
        $this->watcher->subscribe();
    }
}