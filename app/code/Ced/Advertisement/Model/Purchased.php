<?php

namespace Ced\Advertisement\Model;
class Purchased extends \Magento\Framework\Model\AbstractModel
{
	protected $_eventPrefix = 'advertisement_purchased';
	/**
	 * Initialize resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Ced\Advertisement\Model\ResourceModel\Purchased');
	}
}