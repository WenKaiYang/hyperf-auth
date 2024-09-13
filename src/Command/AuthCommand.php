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

namespace ELLa123\HyperfAuth\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Utils\Str;

#[Command]
class AuthCommand extends HyperfCommand
{
    /**
     * 执行的命令行.
     */
    protected $name = 'gen:auth';

    public function configure(): void
    {
        parent::configure();
        $this->setDescription('Create a new authorization key');
    }

    public function handle(): void
    {
        $this->gen('AUTH_SSO_CLIENTS', 'h5,app,pc');
        $this->gen('JWT_SSO_SECRET');
        $this->gen('JWT_SECRET');
    }

    public function gen($key, ?string $value = null): void
    {
        if (empty(env($key))) {
            file_put_contents(
                BASE_PATH . '/.env',
                sprintf(
                    PHP_EOL . '%s=%s',
                    $key,
                    $value ?? hash('sha256', Str::random(32))
                ),
                FILE_APPEND
            );
            $this->info($key . ' 已生成!');
        } else {
            $this->info($key . ' 已存在!');
        }
    }
}
