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

namespace Ced\CsMarketplace\Model\Vendor\Payment\Methods;

/**
 * Class Cheque
 * @package Ced\CsMarketplace\Model\Vendor\Payment\Methods
 */
class Cheque extends AbstractModel
{

    /**
     * @var string
     */
    protected $_code = 'vcheque';

    /**
     * Retrieve input fields
     *
     * @return array
     */
    public function getFields()
    {
        $fields = parent::getFields();
        $fields['cheque_payee_name'] = ['type' => 'text'];
        return $fields;
    }

    /**
     * Retrieve labels
     *
     * @param  string $key
     * @return string
     */
    public function getLabel($key)
    {
        switch ($key) {
            case 'label' :
                return __('Check/Money Order');
            case 'cheque_payee_name' :
                return __('Cheque Payee Name');
            default :
                return parent::getLabel($key);
        }
    }
}
