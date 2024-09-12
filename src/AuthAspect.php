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

use ELLa123\HyperfAuth\Annotation\Auth;
use ELLa123\HyperfAuth\Exception\UnauthorizedException;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;

#[Aspect]
class AuthAspect extends AbstractAspect
{
    public $annotations = [
        Auth::class,
    ];

    #[Inject(AuthManager::class)]
    protected AuthManager $auth;

    /**
     * @throws Exception
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $annotation = $proceedingJoinPoint->getAnnotationMetadata();

        /** @var Auth $authAnnotation */
        $authAnnotation = $annotation->class[Auth::class] ?? $annotation->method[Auth::class];

        $guards = is_array($authAnnotation->value) ? $authAnnotation->value : [$authAnnotation->value];

        foreach ($guards as $name) {
            $guard = $this->auth->guard($name);

            if (! $guard->user() instanceof Authenticatable) {
                throw new UnauthorizedException("Without authorization from {$guard->getName()} guard", $guard);
            }
        }

        return $proceedingJoinPoint->process();
    }
}
