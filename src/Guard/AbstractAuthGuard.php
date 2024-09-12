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

namespace ELLa123\HyperfAuth\Guard;

use ELLa123\HyperfAuth\Authenticatable;
use ELLa123\HyperfAuth\AuthGuard;
use ELLa123\HyperfAuth\Provider\UserProvider;

abstract class AbstractAuthGuard implements AuthGuard
{
    protected array $config;

    protected string $name;

    protected UserProvider $userProvider;

    /**
     * AbstractAuthGuard constructor.
     */
    public function __construct(array $config, string $name, UserProvider $userProvider)
    {
        $this->config = $config;
        $this->name = $name;
        $this->userProvider = $userProvider;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function check(): bool
    {
        return $this->user() instanceof Authenticatable;
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function getProvider(): UserProvider
    {
        return $this->userProvider;
    }

    public function id()
    {
        return $this->user()->getId();
    }
}
