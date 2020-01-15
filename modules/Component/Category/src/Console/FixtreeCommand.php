<?php

namespace GuoJiangClub\Catering\Component\Category\Console;

use GuoJiangClub\Catering\Component\Category\Models\Category;
use Illuminate\Console\Command;

class FixtreeCommand extends Command
{
	protected $signature = 'category:fixtree';

	protected $description = 'fix category old data tree';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		Category::fixTree();
	}
}