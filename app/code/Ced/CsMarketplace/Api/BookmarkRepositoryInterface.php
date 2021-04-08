<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\CsMarketplace\Api;

/**
 * Bookmark CRUD interface
 *
 * @api
 * @since 100.0.2
 */
interface BookmarkRepositoryInterface
{
    /**
     * Save bookmark
     *
     * @param \Ced\CsMarketplace\Api\Data\BookmarkInterface $bookmark
     * @return \Ced\CsMarketplace\Api\Data\BookmarkInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Ced\CsMarketplace\Api\Data\BookmarkInterface $bookmark);

    /**
     * Retrieve bookmark
     *
     * @param int $bookmarkId
     * @return \Ced\CsMarketplace\Api\Data\BookmarkInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($bookmarkId);

    /**
     * Retrieve bookmarks matching the specified criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ced\CsMarketplace\Api\Data\BookmarkSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete bookmark
     *
     * @param \Ced\CsMarketplace\Api\Data\BookmarkInterface $bookmark
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Ced\CsMarketplace\Api\Data\BookmarkInterface $bookmark);

    /**
     * Delete bookmark by ID
     *
     * @param int $bookmarkId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($bookmarkId);
}
