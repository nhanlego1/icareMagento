<?php

namespace Icare\IcareOrderApi\Api\Data;

interface GetOrderDetailInfoInterface
{
	/**
	 * @return int
	 */
	public function getOrderid();

	/**
	 * @param int $orderId 
	 */
	public function setOrderid($orderId);

	/**
	 * @return string
	 */
	public function getOrderincrementid();
	
	/**
	 * @param string $orderIncrementId 
	 */
	public function setOrderincrementid($orderIncrementId);
}
?>