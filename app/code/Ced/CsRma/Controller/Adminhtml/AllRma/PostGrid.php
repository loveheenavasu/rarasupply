<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsRma
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */ 
namespace Ced\CsRma\Controller\Adminhtml\AllRma;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\Controller\ResultFactory;


class PostGrid extends \Magento\Backend\App\Action
{
	/**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @param Magento\Backend\App\Action\Context $context
     * @param Magento\Framework\View\LayoutFactory $layoutFactory
     */ 
	public function __construct(
			Context $context,
			LayoutFactory $layoutFactory	
	) {
		$this->layoutFactory = $layoutFactory;
		parent::__construct($context);
		
	}

	/**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $layout = $this->layoutFactory->create();
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $resultRaw->setContents($layout->createBlock('Ced\CsRma\Block\Adminhtml\AllRma\Order\Grid')->toHtml());
        return $resultRaw;
    }


}

