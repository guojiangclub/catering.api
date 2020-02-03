<?php

namespace GuoJiangClub\EC\Catering\Backend\Console;

use iBrand\Wechat\Backend\Seeds\WechatBackendTablesSeeder;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
	protected $signature = 'ibrand:catering-install';

	protected $description = 'install ibrand\'s catering backend system.';

	public function handle()
	{
		$this->call('ibrand:backend-install');
		$this->call('ibrand:backend-install-extensions');
		$this->call('import:catering-backend-menus');
		$this->call('db:seed', ['--class' => WechatBackendTablesSeeder::class]);
		$this->call('shitang:backend-menus');
		$this->call('ibrand:store-default-value');
		$this->call('ibrand:store-default-specs');
		$this->call('roles:factory');
	}
}