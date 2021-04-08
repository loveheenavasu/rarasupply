<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\CsMarketplace\Controller\Bookmark;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Element\UiComponentFactory;
use Ced\CsMarketplace\Api\BookmarkManagementInterface;
use Ced\CsMarketplace\Api\BookmarkRepositoryInterface;
use Magento\Framework\App\Action\Action;

/**
 * Class Delete action
 */
class Delete extends Action
{
    /**
     * @var BookmarkRepositoryInterface
     */
    protected $bookmarkRepository;

    /**
     * @var BookmarkManagementInterface
     */
    private $bookmarkManagement;

    /**
     * @param Context $context
     * @param UiComponentFactory $factory
     * @param BookmarkRepositoryInterface $bookmarkRepository
     * @param BookmarkManagementInterface $bookmarkManagement
     */
    public function __construct(
        Context $context,
        UiComponentFactory $factory,
        BookmarkRepositoryInterface $bookmarkRepository,
        BookmarkManagementInterface $bookmarkManagement
    ) {
        parent::__construct($context);
        $this->bookmarkRepository = $bookmarkRepository;
        $this->bookmarkManagement = $bookmarkManagement;
    }

    /**
     * Action for AJAX request
     *
     * @return void
     */
    public function execute()
    {
        $viewIds = explode('.', $this->_request->getParam('data'));
        $bookmark = $this->bookmarkManagement->getByIdentifierNamespace(
            array_pop($viewIds),
            $this->_request->getParam('namespace')
        );

        if ($bookmark && $bookmark->getId()) {
            $this->bookmarkRepository->delete($bookmark);
        }
    }
}
