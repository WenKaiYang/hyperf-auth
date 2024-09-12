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

namespace HyperfTest;

use ELLa123\HyperfAuth\Authenticatable;

class DemoUser implements Authenticatable
{
    public $id;

    /**
     * DemoUser constructor.
     * @param mixed $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public static function retrieveById($key): ?Authenticatable
    {
        return new DemoUser($key);
    }
}
