<?php

namespace GuoJiangClub\EC\Catering\Backend\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
	protected $signature = 'ibrand:catering-install';

	protected $description = 'install ibrand\'s catering backend system.';

	public function handle()
	{
		$this->call('import:catering-backend-menus');
		//$this->call('roles:factory');
	}
}