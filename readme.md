### 果酱餐饮

果酱餐饮是为餐饮商户和个人提供的支持在线充值、买单服务和会员管理的平台。

### 效果截图

![果酱餐饮](https://cdn.guojiang.club/catering.jpg)

### 功能列表

- 会员管理
- 促销活动管理
- 优惠券
- 在线核销
- 在线充值
- 积分商城
- 订单管理

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

## 小程序

小程序源码地址：[果酱餐饮小程序源码](https://github.com/guojiangclub/catering.miniprogram)

## 交流

扫码添加[玖玖|彼得助理]，可获得“陈彼得”绝密编程资源以及25个副业赚钱思维。

![玖玖|彼得助理 微信二维码](https://cdn.guojiang.club/xiaojunjunqyewx2.jpg)