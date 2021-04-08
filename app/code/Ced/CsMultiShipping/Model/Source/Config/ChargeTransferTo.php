<?php


namespace Ced\CsMultiShipping\Model\Source\Config;


use Magento\Framework\Data\OptionSourceInterface;

class ChargeTransferTo implements OptionSourceInterface
{

    const TYPE_VENDOR = 'vendor';
    const TYPE_ADMIN = 'admin';

    /**
     * {@inheritDoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::TYPE_VENDOR, 'label' => __(ucfirst(self::TYPE_VENDOR))],
            ['value' => self::TYPE_ADMIN, 'label' => __(ucfirst(self::TYPE_ADMIN))]
        ];
    }
}
