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
use ELLa123\HyperfAuth\Events\ForcedOfflineEvent;
use ELLa123\HyperfAuth\Provider\UserProvider;
use ELLa123\HyperfJwt\Exceptions\InvalidTokenException;
use ELLa123\HyperfJwt\Exceptions\JWTException;
use ELLa123\HyperfJwt\Exceptions\SignatureException;
use ELLa123\HyperfJwt\Exceptions\TokenExpiredException;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Redis\Redis;
use Psr\EventDispatcher\EventDispatcherInterface;

class SsoGuard extends JwtGuard
{
    /**
     * @var Redis
     */
    protected mixed $redis;

    /**
     * @var EventDispatcherInterface
     */
    protected mixed $eventDispatcher;

    public function __construct(array $config, string $name, UserProvider $userProvider, RequestInterface $request)
    {
        parent::__construct($config, $name, $userProvider, $request);
        $this->eventDispatcher = make(EventDispatcherInterface::class);

        // 初始化redis实例
        $this->redis = is_callable($config['redis']) ? call_user_func_array($config['redis'], []) : make(Redis::class);
    }

    public function getClients(): array
    {
        return $this->config['clients'] ?? ['unknown'];
    }

    /**
     * @throws InvalidTokenException
     * @throws SignatureException
     * @throws \RedisException
     */
    public function login(Authenticatable $user, array $payload = [], ?string $client = null): string
    {
        $client = $client ?: $this->getClients()[0]; // 需要至少配置一个客户端
        $token = parent::login($user, $payload);
        $redisKey = str_replace('{uid}', (string) $user->getId(), $this->config['redis_key'] ?? 'u:token:{uid}');

        if (! empty($previousToken = $this->redis->hGet($redisKey, $client)) && $previousToken != $token) {
            // 如果存在上一个 token，就给他拉黑，也就是强制下线
            Context::set($this->resultKey($previousToken), null);
            $this->getJwtManager()->addBlacklist($this->getJwtManager()->justParse($previousToken));
            $this->eventDispatcher->dispatch(new ForcedOfflineEvent($user, $client));
        }

        $this->redis->hSet($redisKey, $client, $token);

        return $token;
    }

    /**
     * @throws SignatureException
     * @throws \RedisException
     * @throws JWTException
     * @throws InvalidTokenException
     * @throws TokenExpiredException
     */
    public function refresh(?string $token = null, ?string $client = null): ?string
    {
        $token = parent::refresh($token);

        if ($token) {
            $client = $client ?: $this->getClients()[0]; // 需要至少配置一个客户端
            $redisKey = str_replace('{uid}', (string) $this->id($token), $this->config['redis_key'] ?? 'u:token:{uid}');
            $this->redis->hSet($redisKey, $client, $token);
        }

        return $token;
    }
}
