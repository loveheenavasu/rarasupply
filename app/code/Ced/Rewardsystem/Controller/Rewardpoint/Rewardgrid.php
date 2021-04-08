<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Rewardsystem
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Rewardsystem\Controller\Rewardpoint;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Rewardgrid extends \Magento\Framework\App\Action\Action
{
	/**
	 * @var PageFactory
	 */
	protected $resultPageFactory;
	/**
	 * @var \Magento\Framework\Data\Form\FormKey
	 */
	protected $formKey;
	/**
	 * @param Context $context
	 * @param PageFactory $resultPageFactory
	 */
	public function __construct(
			Context $context,
			\Magento\Framework\Data\Form\FormKey $formKey,
			PageFactory $resultPageFactory
	) {

		parent::__construct($context);
		$this->formKey = $formKey;
		$this->resultPageFactory = $resultPageFactory;
	}
	/**
	 *
	 * @return \Magento\Framework\View\Result\Page
	 */
	public function execute()
	{

		$resultPage = $this->resultPageFactory->create();
	    return $resultPage;
	}
}