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
use RuntimeException;

class EloquentProvider extends AbstractUserProvider
{
    public function retrieveByCredentials(mixed $credentials): ?Authenticatable
    {
        if (empty($this->config['model'])) {
            throw new RuntimeException('Please configure model');
        }

        if (!method_exists($this->config['model'], 'retrieveById')) {
            throw new RuntimeException('The Authenticatable interface is not implemented in the ' . $this->config['model'] . ' model');
        }

        return call_user_func_array([$this->config['model'], 'retrieveById'], [$credentials]);
    }

    public function validateCredentials(Authenticatable $user, mixed $credentials): bool
    {
        return $user->getId() === $credentials;
    }
}
