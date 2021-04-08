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
 * @package     Ced_CsMultiShipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */


namespace Ced\CsMultiShipping\Observer;


use Magento\Framework\Event\ObserverInterface;

class SaveChargeTransferTo implements ObserverInterface
{
    const XML_PATH_CHARGE_TRANSFER_TO = 'ced_csmultishipping/general/charge_transfer_to';

    protected $dataHelper;

    public function __construct(
        \Ced\CsMarketplace\Helper\Data $dataHelper
    )
    {
        $this->dataHelper = $dataHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/shipping.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(__FILE__);
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        $chargeTransferTo = $this->dataHelper->getStoreConfig(
            self::XML_PATH_CHARGE_TRANSFER_TO
        );
        $logger->info('$chargeTransferTo');
        $logger->info($chargeTransferTo);
        $quote->setChargeTransferTo($chargeTransferTo);
        $order->setChargeTransferTo($chargeTransferTo);
    }
}
