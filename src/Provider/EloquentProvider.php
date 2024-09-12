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

use ELLa123\HyperfAuth\Authenticatable;

class EloquentProvider extends AbstractUserProvider
{
    public function retrieveByCredentials(mixed $credentials): ?Authenticatable
    {
        return call_user_func_array([$this->config['model'], 'retrieveById'], [$credentials]);
    }

    public function validateCredentials(Authenticatable $user, mixed $credentials): bool
    {
        return $user->getId() === $credentials;
    }
}
