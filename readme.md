### 果酱餐饮

果酱餐饮是为餐饮商户和个人提供的支持在线充值、买单服务和会员管理的平台。

目前只提供移动端 H5，不包含小程序，PC和APP.

### 安装

```shell
git clone git@gitlab.guojiang.club:guojiangclub/catering.api.git

composer install

cp .env.example .env    # 请配置好数据库，APP_URL等配置项

php artisan vendor:publish --all
 
php artisan ibrand:catering-install

php artisan key:generate

php artisan storage:link

php artisan passport:install

chmod -R 0777 storage

chmod -R 0777 bootstrap

```

