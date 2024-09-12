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

namespace ELLa123\HyperfAuth\Provider;

use ELLa123\HyperfAuth\UserProvider;

abstract class AbstractUserProvider implements UserProvider
{
    protected array $config;

    protected string $name;

    /**
     * AbstractUserProvider constructor.
     */
    public function __construct(array $config, string $name)
    {
        $this->config = $config;
        $this->name = $name;
    }
}
