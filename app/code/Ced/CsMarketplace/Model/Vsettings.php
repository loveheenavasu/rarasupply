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
 * @package     Ced_CsMarketplace
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Model;


/**
 * Class Vsettings
 * @package Ced\CsMarketplace\Model
 */
class Vsettings extends \Ced\CsMarketplace\Model\FlatAbstractModel
{

    const PAYMENT_SECTION = 'payment';

    /**
     * @var mixed
     */
    protected $_serializer;

    /**
     * @return mixed
     */
    public function getSerializer()
    {
        return $this->_serializer;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        if ($this->getId() && $this->getSerialized()) {
            $value = $this->getSerializer()->unserialize($this->getData('value'));
            return $value;
        }
        return $this->getData('value');
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ced\CsMarketplace\Model\ResourceModel\Vsettings');
    }
}
