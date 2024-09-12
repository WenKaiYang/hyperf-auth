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
use ELLa123\HyperfAuth\Exception\AuthException;
use ELLa123\HyperfAuth\Exception\UnauthorizedException;
use ELLa123\HyperfAuth\Provider\UserProvider;
use Hyperf\Context\Context;
use Hyperf\Contract\SessionInterface;

class SessionGuard extends AbstractAuthGuard
{
    protected SessionInterface $session;

    /**
     * JwtGuardAbstract constructor.
     */
    public function __construct(array $config, string $name, UserProvider $userProvider, SessionInterface $session)
    {
        parent::__construct($config, $name, $userProvider);
        $this->session = $session;
    }

    public function login(Authenticatable $user): bool
    {
        $this->session->put($this->sessionKey(), $user->getId());

        Context::set($this->resultKey(), $user);

        return true;
    }

    public function resultKey(): string
    {
        return $this->name . 'auth.result:' . $this->session->getId();
    }

    /**
     * @throws \Throwable
     */
    public function user(): ?Authenticatable
    {
        if (Context::has($key = $this->resultKey())) {
            $result = Context::get($key);
            if ($result instanceof \Throwable) {
                throw $result;
            }
            return $result ?: null;
        }

        try {
            if ($credentials = $this->session->get($this->sessionKey())) {
                $user = $this->userProvider->retrieveByCredentials($credentials);
                Context::set($key, $user ?? 0);
                return $user;
            }
            throw new UnauthorizedException('Unauthorized.', $this);
        } catch (\Throwable $exception) {
            $newException = $exception instanceof AuthException ? $exception : new UnauthorizedException(
                $exception->getMessage(),
                $this,
                $exception
            );
            Context::set($key, $newException);
            throw $newException;
        }
    }

    public function id()
    {
        return $this->session->get($this->sessionKey());
    }

    /**
     * @throws \Throwable
     */
    public function check(): bool
    {
        try {
            return $this->user() instanceof Authenticatable;
        } catch (AuthException $exception) {
            return false;
        }
    }

    public function logout(): bool
    {
        Context::set($this->resultKey(), null);
        return (bool) $this->session->remove($this->sessionKey());
    }

    protected function sessionKey(): string
    {
        return 'auth_' . $this->name;
    }
}
