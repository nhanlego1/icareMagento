<?php

namespace Icare\IcareOrderApi\Api\Data;

interface OptionValueInterface
{
	/**
	 * @return int
	 */
	public function getId();

	/**
	 * @param int $id 
	 */
	public function setId($id);

	/**
	 * @return int
	 */
	public function getValue();
	
	/**
	 * @param string $value 
	 */
	public function setValue($value);
}
?>