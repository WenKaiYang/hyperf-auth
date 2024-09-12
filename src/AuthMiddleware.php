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

use ELLa123\HyperfAuth\Exception\UnauthorizedException;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class AuthMiddleware.
 */
class AuthMiddleware implements MiddlewareInterface
{
    protected array $guards = [null];

    #[Inject(AuthManager::class)]
    protected AuthManager $auth;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        foreach ($this->guards as $name) {
            $guard = $this->auth->guard($name);

            if (! $guard->user() instanceof Authenticatable) {
                throw new UnauthorizedException("Without authorization from {$guard->getName()} guard", $guard);
            }
        }

        return $handler->handle($request);
    }
}
