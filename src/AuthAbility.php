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

use Hyperf\Database\Model\Model;

/**
 * Trait AuthAbility.
 * @mixin Authenticatable|Model
 */
trait AuthAbility
{
    public function getId()
    {
        return $this->getKey();
    }

    public static function retrieveById($key): ?Authenticatable
    {
        /* @var Authenticatable */
        return self::query()->find($key);
    }
}
