<?php

namespace Ced\Advertisement\Model;
class Positions extends \Magento\Framework\Model\AbstractModel
{
	protected $_eventPrefix = 'advertisement_positions';
	/**
	 * Initialize resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Ced\Advertisement\Model\ResourceModel\Positions');
	}
}