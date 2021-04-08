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
 * Class Banktransfer
 * @package Ced\CsMarketplace\Model\Vendor\Payment\Methods
 */
class Banktransfer extends AbstractModel
{

    /**
     * @var string
     */
    protected $_code = 'vbanktransfer';

    /**
     * Retrieve input fields
     *
     * @return array
     */
    public function getFields()
    {
        $fields = parent::getFields();
        $fields['bank_name'] = ['type' => 'text'];
        $fields['bank_branch_number'] = ['type' => 'text'];
        $fields['bank_swift_code'] = ['type' => 'text'];
        $fields['bank_account_name'] = ['type' => 'text'];
        $fields['bank_account_number'] = ['type' => 'text'];
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
                return __('Bank Transfer');
            case 'bank_name' :
                return __('Bank Name');
            case 'bank_branch_number' :
                return __('Bank Branch Number');
            case 'bank_swift_code' :
                return __('Bank Swift Code');
            case 'bank_account_name' :
                return __('Bank Account Name');
            case 'bank_account_number' :
                return __('Bank Account Number');
            default :
                return parent::getLabel($key);
        }
    }
}
