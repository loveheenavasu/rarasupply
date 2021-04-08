<?php

namespace Ced\Advertisement\Controller\Adminhtml\Plan;

class NewAction extends \Magento\Backend\App\Action
{
    /**
     * New promo quote action
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
