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

namespace ELLa123\HyperfAuth;

use ELLa123\HyperfAuth\Provider\UserProvider;

interface AuthGuard
{
    public function id();

    public function login(Authenticatable $user);

    public function user(): ?Authenticatable;

    public function check(): bool;

    public function guest(): bool;

    public function logout();

    public function getProvider(): UserProvider;

    public function getName(): string;
}
