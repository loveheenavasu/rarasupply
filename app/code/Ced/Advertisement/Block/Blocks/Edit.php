<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\Advertisement\Block\Blocks;

/**
 * Customer dashboard block
 *
 * @api
 * @since 100.0.2
 */
class Edit extends \Magento\Framework\View\Element\Template
{
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    public function getBlocksData(){        
        $coll = $this->_coreRegistry->registry('advertisementBlock');
        return $coll;
    }

    public function getBlockId(){
        return $this->getRequest()->getParam('id');
    }
}