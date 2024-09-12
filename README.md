# qbhy/hyperf-auth

hyperf 的 auth 组件，目前支持 jwt、session、sso 驱动。用户可以自行扩展。  
本组件参考了 laravel 的 auth 组件设计，使用体验大体和 laravel 的 auth 差不多。

[![Latest Stable Version](https://poser.pugx.org/ella123/hyperf-auth/v/stable)](https://packagist.org/packages/ella123/hyperf-auth)
[![Total Downloads](https://poser.pugx.org/ella123/hyperf-auth/downloads)](https://packagist.org/packages/ella123/hyperf-auth)
[![Latest Unstable Version](https://poser.pugx.org/ella123/hyperf-auth/v/unstable)](https://packagist.org/packages/ella123/hyperf-auth)
[![License](https://poser.pugx.org/ella123/hyperf-auth/license)](https://packagist.org/packages/ella123/hyperf-auth)
[![Monthly Downloads](https://poser.pugx.org/ella123/hyperf-auth/d/monthly)](https://packagist.org/packages/ella123/hyperf-auth)
[![Daily Downloads](https://poser.pugx.org/ella123/hyperf-auth/d/daily)](https://packagist.org/packages/ella123/hyperf-auth)

## 支持的验证方式

* session
* jwt
* sso （单点登录，继承自jwt，需要 hyperf/redis，支持多客户端）


## 安装 - install

```bash
composer require ella123/hyperf-auth
```

## 配置 - configuration

使用 `ELLa123\HyperfAuth\AuthExceptionHandler` ，此步骤可选，开发者可以自行捕捉 `AuthException`  和 `JWTException` 进行处理

```php
<?php
// config/autoload/exceptions.php
return [
    'handler' => [
        'http' => [
            \ELLa123\HyperfAuth\AuthExceptionHandler::class,
        ],    
    ],
];
```

发布配置 vendor:publish

```bash
php bin/hyperf.php vendor:publish ella123/hyperf-auth
```

生成 `.env` 配置项

```bash
php bin/hyperf.php gen:auth-env
```

## 使用 - usage

> 以下是伪代码，仅供参考。Auth 注解可以用于类或者方法。

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use ELLa123\HyperfAuth\Annotation\Auth;
use ELLa123\HyperfAuth\AuthManager;

/**
* @Controller
* Class IndexController
*/
class IndexController extends AbstractController
{
  /**
   * @Inject
   * @var AuthManager
   */
  protected $auth;

  /**
   * @GetMapping(path="/login")
   * @return array
   */
  public function login()
  {
      /** @var User $user */
      $user = User::query()->firstOrCreate(['name' => 'test', 'avatar' => 'avatar']);
      return [
          'status' => $this->auth->guard('session')->login($user),
      ];
  }
  
  /**
   * @GetMapping(path="/sso/login")
   * @return array
   */
  public function ssoLogin()
  {
      /** @var User $user */
      $user = User::query()->firstOrCreate(['name' => 'test', 'avatar' => 'avatar']);
      return [
          'token' => $this->auth->guard('sso')->login($user, [],  'pc'), // sso 方法支持第二个参数，传定义好的客户端
      ];
  }

  /**
   * @Auth("session")
   * @GetMapping(path="/logout")
   */
  public function logout()
  {
      $this->auth->guard('session')->logout();
      return 'logout ok';
  }

  /**
   * 使用 Auth 注解可以保证该方法必须通过某个 guard 的授权，支持同时传多个 guard，不传参数使用默认 guard
   * @Auth("session")
   * @GetMapping(path="/user")
   * @return string
   */
  public function user()
  {
      $user = $this->auth->guard('session')->user();
      return 'hello '.$user->name;
  }
}
```

除了上面的 Auth 注解用法，还支持中间件用法

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\Middleware;
use ELLa123\HyperfAuth\AuthMiddleware; 
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\GetMapping;
use ELLa123\HyperfAuth\AuthManager;

/**
* @Middleware(AuthMiddleware::class)
* Class IndexController
*/
class IndexController extends AbstractController
{
  /**
   * @Inject
   * @var AuthManager
   */
  protected $auth;
  
  /**
   * @GetMapping(path="/user")
   * @return string
   */
  public function user()
  {
      $user = $this->auth->guard()->user();
      return 'hello '.$user->name;
  }
}
```

由于 hyperf 还不支持中间件传参，所以 `ELLa123\HyperfAuth\AuthMiddleware` 中间件只支持默认guard校验  
但是开发者可以继承该中间自行扩展。或者直接使用 Auth 注解进行自定义 guard 校验，与中间件的效果是一致的。

```php
<?php

declare(strict_types=1);

use ELLa123\HyperfAuth\AuthMiddleware; 

class SessionAuthMiddleware extends AuthMiddleware { 
    protected $guards = ['session']; // 支持多个 guard

 }
```

## 更多用法 - API

```php
<?php

$auth = auth(); // 控制器内也可以通过 @Inject 注入

$user = new \HyperfTest\DemoUser(1);

// 直接调用 AuthGuard 方法，这种情况下会获取 默认 guard 然后调用 guard 的对应方法
$auth->login($user); // 默认使用 jwt 驱动，该方法将返回 token 字符串
$auth->logout(); // 退出登录
$auth->check(); // 检查是否登录
$auth->id(); // 获取当前登录用户的id，无需查数据库
$auth->guest(); // 是否游客/是否未登录
$auth->user(); // 若登录返回当前登录用户，否则返回null

/** @var \ELLa123\HyperfAuth\Guard\JwtGuard $jwtGuard */
$jwtGuard = $auth->guard('jwt');
$jwtGuard->user('your token or null'); // jwt 驱动支持手动传入 token，如不传或者传null则从 request 中解析
$jwtGuard->check('your token or null');
$jwtGuard->id('your token or null'); // 无需查数据库
$jwtGuard->guest('your token or null');
$jwtGuard->refresh('your token or null'); // 该方法返回新的 token 或者 null
$jwtGuard->login($user, ['sub' => 'qbhy0715','iss' => 'hyperf-auth',]); // 自定义payload
$jwtGuard->getPayload('your token or null'); // 获取自定义 payload

$auth->guard()->login($user); // guard 方法不传参数或者传null都将使用默认值

// 使用 session 驱动需要安装 hyperf/session 并启用 session
$auth->guard('session')->login($user); // guard 方法不传参数或者传null都会获取默认值
```

> 注意事项：使用 jwt 驱动且令牌异常的情况下调用 user 方法，会抛出相应的异常，需要自行捕捉处理，不想抛异常，可以调用 check
> 进行判断。
