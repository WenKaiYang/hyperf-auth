<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace ELLa123\HyperfAuth\Events;

use ELLa123\HyperfAuth\Authenticatable;

/**
 * 被迫下线事件
 * Class ForcedOfflineEvent.
 */
class ForcedOfflineEvent
{
    /**
     * 用户实例.
     */
    public Authenticatable $user;

    /**
     * 客户端标识.
     */
    public string $client;

    /**
     * ForcedOfflineEvent constructor.
     */
    public function __construct(Authenticatable $user, string $client)
    {
        $this->user = $user;
        $this->client = $client;
    }
}
