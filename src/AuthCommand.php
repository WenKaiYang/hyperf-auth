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

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;

#[Command]
class AuthCommand extends HyperfCommand
{
    /**
     * 执行的命令行.
     */
    protected $name = 'gen:auth-env';

    public function handle(): void
    {
        $this->gen('AUTH_SSO_CLIENTS', 'h5,weapp');
        $this->gen('SSO_JWT_SECRET');
        $this->gen('SIMPLE_JWT_SECRET');
    }

    public function gen($key, ?string $value = null): void
    {
        if (empty(env($key))) {
            file_put_contents(BASE_PATH . '/.env', sprintf(PHP_EOL . '%s=%s', $key, $value ?? str_random(16)), FILE_APPEND);
            $this->info($key . ' 已生成!');
        } else {
            $this->info($key . ' 已存在!');
        }
    }
}
