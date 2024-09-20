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
use ELLa123\HyperfJwt\Exceptions\InvalidTokenException;
use ELLa123\HyperfJwt\Exceptions\JWTException;
use ELLa123\HyperfJwt\Exceptions\SignatureException;
use ELLa123\HyperfJwt\Exceptions\TokenBlacklistException;
use ELLa123\HyperfJwt\Exceptions\TokenExpiredException;
use ELLa123\HyperfJwt\Exceptions\TokenNotActiveException;
use ELLa123\HyperfJwt\JWTManager;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Str;
use Psr\SimpleCache\InvalidArgumentException;

class JwtGuard extends AbstractAuthGuard
{
    protected JWTManager $jwtManager;

    protected RequestInterface $request;

    protected mixed $headerName = 'Authorization';

    /**
     * JwtGuardAbstract constructor.
     */
    public function __construct(
        array $config,
        string $name,
        UserProvider $userProvider,
        RequestInterface $request
    ) {
        parent::__construct($config, $name, $userProvider);
        $this->headerName = $config['header_name'] ?? 'Authorization';
        $this->jwtManager = new JWTManager($config);
        $this->request = $request;
    }

    public function parseToken(): ?string
    {
        $header = $this->request->header($this->headerName, '');
        if (Str::startsWith($header, 'Bearer ')) {
            return Str::substr($header, 7);
        }

        if ($this->request->has('token')) {
            return $this->request->input('token');
        }

        return null;
    }

    public function login(Authenticatable $user, array $payload = []): string
    {
        $token = $this->getJwtManager()->make(array_merge($payload, [
            'uid' => $user->getId(),
            's' => Str::random(32),
        ]))->token();

        Context::set($this->resultKey($token), $user);

        return $token;
    }

    /**
     * 获取用于存到 context 的 key.
     */
    public function resultKey(mixed $token): string
    {
        return $this->name . '.auth.result' . $this->getJti($token);
    }

    /**
     * @throws \Throwable
     * @throws SignatureException
     * @throws InvalidTokenException
     * @throws TokenExpiredException
     */
    public function user(?string $token = null): ?Authenticatable
    {
        $token = $token ?? $this->parseToken();
        if (Context::has($key = is_string($token) ? $this->resultKey($token) : '_nothing')) {
            $result = Context::get($key);
            if ($result instanceof UnauthorizedException) {
                throw $result;
            }
            return $result ?: null;
        }

        try {
            if ($token) {
                $jwt = $this->getJwtManager()->parse($token);
                $uid = $jwt->getPayload()['uid'] ?? null;
                $user = $uid ? $this->userProvider->retrieveByCredentials($uid) : null;
                Context::set($key, $user ?: 0);

                return $user;
            }

            throw new UnauthorizedException('The token is required.', $this);
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

    /**
     * @throws \Throwable
     * @throws SignatureException
     * @throws InvalidTokenException
     * @throws TokenExpiredException
     */
    public function check(?string $token = null): bool
    {
        try {
            return $this->user($token) instanceof Authenticatable;
        } catch (AuthException $exception) {
            return false;
        }
    }

    /**
     * @throws \Throwable
     * @throws SignatureException
     * @throws InvalidTokenException
     * @throws TokenExpiredException
     */
    public function guest(?string $token = null): bool
    {
        return ! $this->check($token);
    }

    /**
     * 刷新 token，旧 token 会失效.
     *
     * @throws InvalidTokenException
     * @throws JWTException
     * @throws SignatureException
     * @throws InvalidArgumentException
     */
    public function refresh(?string $token = null): ?string
    {
        $token = $token ?: $this->parseToken();

        if ($token) {
            Context::set($this->resultKey($token), null);

            try {
                $jwt = $this->getJwtManager()->parse($token);
            } catch (TokenExpiredException $exception) {
                $jwt = $exception->getJwt();
            }

            $this->getJwtManager()->addBlacklist($jwt);

            return $this->getJwtManager()->refresh($jwt)->token();
        }

        return null;
    }

    /**
     * @throws InvalidTokenException
     * @throws TokenExpiredException
     * @throws SignatureException
     * @throws TokenNotActiveException
     * @throws TokenBlacklistException
     * @throws InvalidArgumentException
     */
    public function logout(?string $token = null): bool
    {
        if ($token = $token ?? $this->parseToken()) {
            Context::set($this->resultKey($token), null);
            $this->getJwtManager()->addBlacklist(
                $this->getJwtManager()->parse($token)
            );
            return true;
        }
        return false;
    }

    /**
     * @param null|mixed $token
     * @throws InvalidTokenException
     * @throws SignatureException
     */
    public function getPayload(?string $token = null): ?array
    {
        if ($token = $token ?? $this->parseToken()) {
            return $this->getJwtManager()->justParse($token)->getPayload();
        }
        return null;
    }

    /**
     * @throws InvalidTokenException
     * @throws SignatureException
     */
    public function getPayloadExpires(?string $token = null): int
    {
        return (int) ($this->getPayload($token)['exp'] ?? 0);
    }

    public function getJwtManager(): JWTManager
    {
        return $this->jwtManager;
    }

    /**
     * @throws InvalidTokenException
     * @throws SignatureException
     * @throws TokenExpiredException
     * @throws TokenBlacklistException
     * @throws TokenNotActiveException
     * @throws InvalidArgumentException
     */
    public function id(?string $token = null): mixed
    {
        if ($token = $token ?? $this->parseToken()) {
            return $this->getJwtManager()->parse($token)->getPayload()['uid'];
        }
        return null;
    }

    /**
     * 获取 token 标识.
     * 为了性能，直接 md5.
     */
    protected function getJti(string $token): string
    {
        return md5($token);
    }
}
