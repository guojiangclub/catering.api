### DMP 系统 API Server 端

### 安装

#### 1.安装 component 和 server 包

在 composer.json 文件下添加如下代码：
```
"element-vip/user": "dev-master",
"element-vip/point": "dev-master",
"element-vip/order": "dev-master",
"element-vip/discount": "dev-master",
"element-vip/server": "dev-master"
```
添加后执行：`composer update -vvv`

> 可能会存在失败的情况，因为目前都是私有包，需要SSH key 添加的白名单，具体请咨询管理员。

#### 2.配置 `config/app.php ` 

`providers` 添加

```
Dingo\Api\Provider\LaravelServiceProvider::class,
LucaDegasperi\OAuth2Server\Storage\FluentStorageServiceProvider::class,
LucaDegasperi\OAuth2Server\OAuth2ServerServiceProvider::class,
ElementVip\Server\Providers\RouteServiceProvider::class,
ElementVip\Server\Providers\OAuthServiceProvider::class,
ElementVip\Component\Point\PointServiceProvider::class,
```
`aliases` 添加
```
'Authorizer' => LucaDegasperi\OAuth2Server\Facades\Authorizer::class,
```

#### 3. 执行php artisan vendor:publish 

执行后，会生成 api.php 和 oauth2.php

api.php 在末尾处添加如下代码：
```
   /*
    * 每页默认数量
    */
    'default_per_page' => env('API_DEFAULT_PER_PAGE', 15),

    /*
     * 可接受请求的最大单页数量
     */
    'max_per_page' => env('API_MAX_PER_PAGE', 30),

    /*
     * 接口频率限制
     */
    'rate_limits' => [

        // 访问频率限制
        'access' => [
            'expires' => env('RATE_LIMITS_EXPIRES', 1),
            'limits'  => env('RATE_LIMITS', 60),
        ],

        // 发布频率限制（发帖和评论）
        'publish' => [
            'expires' => env('PUBLISH_RATE_LIMITS_EXPIRES', 1),
            'limits'  => env('PUBLISH_RATE_LIMITS', 10),
        ],
    ],
```
oauth2.php 在 grant_types 配置如下代码

```
'grant_types' => [

        /*
        * 使用 login_token 获取 access_token
        */
        'password' => [
            'class'            => League\OAuth2\Server\Grant\PasswordGrant::class,
            'callback'         => \ElementVip\Server\OAuth\PasswordGrantVerifier::class.'@verify',
            'access_token_ttl' => (int) env('OAUTH_ACCESS_TOKEN_TTL', 7200),
        ],

        'sms_token' => [
            'class'            => ElementVip\Server\OAuth\SmsTokenGrant::class,
            'callback'         => \ElementVip\Server\OAuth\SmsTokenGrantVerifier::class.'@verify',
            'access_token_ttl' => (int) env('OAUTH_ACCESS_TOKEN_TTL', 7200),
        ],

        /*
         * 在用户还未登陆的时候使用，可访问部分资源
         */
        'client_credentials' => [
            'class' => '\League\OAuth2\Server\Grant\ClientCredentialsGrant',
            'access_token_ttl' => 7200
        ],

        /*
         * 使用此授权方法来更新过期的 Token
         */
        'refresh_token' => [
            'class' => '\League\OAuth2\Server\Grant\RefreshTokenGrant',
            'access_token_ttl' => 7200,
            'refresh_token_ttl' => 2592000
        ]
    ],
```

#### 4.配置 .env 文件

```
API_STANDARDS_TREE=vnd
API_PREFIX=api
API_VERSION=v1
API_DEBUG=true
```


