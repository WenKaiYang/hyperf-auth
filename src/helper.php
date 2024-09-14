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
use ELLa123\HyperfAuth\AuthGuard;
use ELLa123\HyperfAuth\AuthManager;
use Hyperf\Context\ApplicationContext;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

if (! function_exists('auth')) {
    /**
     * 建议视图中使用该函数，其他地方请使用注入.
     * @return AuthGuard|AuthManager|mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function auth(?string $guard = null): mixed
    {
        $auth = ApplicationContext::getContainer()->get(AuthManager::class);

        if (is_null($guard)) {
            return $auth;
        }

        return $auth->guard($guard);
    }
}
