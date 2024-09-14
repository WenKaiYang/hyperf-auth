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

use ELLa123\HyperfAuth\Exception\GuardException;
use ELLa123\HyperfAuth\Exception\UserProviderException;
use ELLa123\HyperfAuth\Provider\UserProvider;
use Hyperf\Contract\ConfigInterface;

use function Hyperf\Support\make;

/**
 * Class AuthManager.
 * @method login(Authenticatable $user)
 * @method null|Authenticatable user($token = null)
 * @method bool check($token = null)
 * @method logout()
 * @method string getName()
 * @method bool guest()
 * @method getProvider()
 * @method id($token = null)
 * @mixin AuthGuard
 */
class AuthManager
{
    protected string $defaultDriver = 'default';

    protected array $guards = [];

    protected array $providers = [];

    protected array $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config->get('auth');
    }

    public function __call($name, $arguments)
    {
        $guard = $this->guard();

        if (method_exists($guard, $name)) {
            return call_user_func_array([$guard, $name], $arguments);
        }

        throw new GuardException('Method not defined. method:' . $name);
    }

    /**
     * @throws GuardException
     * @throws UserProviderException
     */
    public function guard(?string $name = null): AuthGuard
    {
        $name = $name ?? $this->defaultGuard();

        if (empty($this->config['guards'][$name])) {
            throw new GuardException("Does not support this driver: {$name}");
        }

        $config = $this->config['guards'][$name];
        $userProvider = $this->provider($config['provider'] ?? $this->defaultDriver);

        return $this->guards[$name] ?? $this->guards[$name] = make(
            $config['driver'],
            compact('name', 'config', 'userProvider')
        );
    }

    /**
     * @throws UserProviderException
     */
    public function provider(?string $name = null): UserProvider
    {
        $name = $name ?? $this->defaultProvider();

        if (empty($this->config['providers'][$name])) {
            throw new UserProviderException("Does not support this provider: {$name}");
        }

        $config = $this->config['providers'][$name];

        return $this->providers[$name] ?? $this->providers[$name] = make(
            $config['driver'],
            [
                'config' => $config,
                'name' => $name,
            ]
        );
    }

    public function defaultGuard(): string
    {
        return $this->config['default']['guard'] ?? $this->defaultDriver;
    }

    public function defaultProvider(): string
    {
        return $this->config['default']['provider'] ?? $this->defaultDriver;
    }

    public function getGuards(): array
    {
        return $this->guards;
    }
}
