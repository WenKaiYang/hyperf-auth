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

interface UserProvider
{
    /**
     * Retrieve a user by the given credentials.
     */
    public function retrieveByCredentials(mixed $credentials): ?Authenticatable;

    /**
     * Validate a user against the given credentials.
     */
    public function validateCredentials(Authenticatable $user, mixed $credentials): bool;
}
