<?php

namespace GuoJiangClub\Catering\Component\Shipping\Console;

use GuoJiangClub\Catering\Component\Shipping\Models\ShippingMethod;
use Illuminate\Console\Command;

class ShippingCommand extends Command
{

	protected $signature = 'shipping:install';

	protected $description = 'create shipping method data.';

	public function handle()
	{
		return $this->createShippingMethodData();
	}

	private function createShippingMethodData()
	{
		ShippingMethod::create(['code' => 'longbangwuliu', 'name' => '龙邦物流', 'url' => 'http://www.lbex.com.cn', 'is_enabled' => true]);
		ShippingMethod::create(['code' => 'ems', 'name' => '邮政特快专递', 'url' => 'http://www.ems.com.cn', 'is_enabled' => true]);
		ShippingMethod::create(['code' => 'tiantian', 'name' => '天天快递', 'url' => 'http://www.ttkd.cn', 'is_enabled' => true]);
		ShippingMethod::create(['code' => 'yunda', 'name' => '韵达快递', 'url' => 'http://www.yundaex.com', 'is_enabled' => true]);
		ShippingMethod::create(['code' => 'zhongtong', 'name' => '中通速递', 'url' => 'http://www.zto.cn', 'is_enabled' => true]);
		ShippingMethod::create(['code' => 'shentong', 'name' => '申通快递', 'url' => 'http://www.sto.cn', 'is_enabled' => true]);
		ShippingMethod::create(['code' => 'yuantong', 'name' => '圆通速递', 'url' => 'http://www.yto.net.cn', 'is_enabled' => true]);
		ShippingMethod::create(['code' => 'debangwuliu', 'name' => '德邦物流', 'url' => 'http://www.deppon.com', 'is_enabled' => true]);
		ShippingMethod::create(['code' => 'shunfeng', 'name' => '顺丰速运', 'url' => 'http://www.sf-express.com', 'is_enabled' => true]);
	}
}