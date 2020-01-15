<?php

namespace GuoJiangClub\Catering\Server\Contracts;

use GuoJiangClub\Catering\Backend\Models\Clerk;

interface UnifiedOrderContracts
{
	public function getName();

	public function unifiedOrder(array $params, $user): array;

	public function refund($order, Clerk $clerk): array;

	public function checkout($order);
}