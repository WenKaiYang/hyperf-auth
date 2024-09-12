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

use Hyperf\Cache\Cache;

/**
 * Class HyperfRedisCache.
 */
class HyperfRedisCache extends Cache
{
    public function fetch($id)
    {
        return $this->driver->get($id);
    }

    public function contains($id): bool
    {
        $exists = $this->driver->has($id);

        if (is_bool($exists)) {
            return $exists;
        }

        return $exists > 0;
    }

    public function save($id, $data, $lifeTime = 0): bool
    {
        if ($lifeTime > 0) {
            return $this->driver->set(key: $id, value: $data, ttl: $lifeTime);
        }

        return $this->driver->set($id, $data);
    }

    public function delete($key): bool
    {
        return $this->driver->delete($key) >= 0;
    }

}
