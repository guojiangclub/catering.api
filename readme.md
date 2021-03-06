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

扫码添加[玖玖|彼得助理]，可获得“陈彼得”为大家精心整理的程序员成长学习路线图，以及前端、Java、Linux、Python等编程学习资料，同时还教你25个副业赚钱思维。

![玖玖|彼得助理 微信二维码](https://cdn.guojiang.club/xiaojunjunqyewx2.jpg)

## 果酱云社区

<p align="center">
  <a href="https://guojiang.club/" target="_blank">
    <img src="https://cdn.guojiang.club/image/2022/02/16/wu_1fs0jbco2182g280l1vagm7be6.png" alt="点击跳转"/>
  </a>
</p>


- 全网真正免费的IT课程平台

- 专注于综合IT技术的在线课程，致力于打造优质、高效的IT在线教育平台

- 课程方向包含Python、Java、前端、大数据、数据分析、人工智能等热门IT课程

- 300+免费课程任你选择


<p align="center">
  <a href="https://guojiang.club/" target="_blank">
    <img src="https://cdn.guojiang.club/image/2022/02/16/wu_1fs0l82ae1pq11e431j6n17js1vq76.png" alt="点击跳转"/>
  </a>
</p>