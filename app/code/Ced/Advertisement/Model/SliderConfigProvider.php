<?php

namespace Ced\Advertisement\Model;


use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\LayoutInterface;

class SliderConfigProvider implements ConfigProviderInterface
{
    /** @var LayoutInterface  */
    protected $_layout;

    public function __construct(LayoutInterface $layout)
    {
        $this->_layout = $layout;
    }

    public function getConfig()
    {
        $cmsBlockId = 1; // id of cms block to use

        return [
            'advertisement_slider_checkout' => $this->_layout->createBlock('Ced\Advertisement\Block\Slider\Position\Checkout')->setBlockId('advertisement_slider_checkout')->toHtml()
        ];
    }
}