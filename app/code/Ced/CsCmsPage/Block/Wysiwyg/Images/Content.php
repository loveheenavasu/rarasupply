<?php
/**
 * CedCommerce
  *
  * NOTICE OF LICENSE
  *
  * This source file is subject to the End User License Agreement (EULA)
  * that is bundled with this package in the file LICENSE.txt.
  * It is also available through the world-wide-web at this URL:
  * http://cedcommerce.com/license-agreement.txt
  *
  * @category    Ced
  * @package     Ced_CsCmsPage
  * @author   CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license      http://cedcommerce.com/license-agreement.txt
  */
namespace Ced\CsCmsPage\Block\Wysiwyg\Images;

/**
 * Wysiwyg Images content block
 *
 * @api
 * @since 100.0.2
 */
class Content extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    /**
     * Block construction
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setData('area','adminhtml');
        parent::_construct();
        $this->_headerText = __('Media Storage');
        $this->buttonList->remove('back');
        $this->buttonList->remove('edit');
        $this->buttonList->add(
            'cancel',
            ['class' => 'cancel  action-quaternary', 'label' => __('Cancel'), 'type' => 'button',
                'onclick' => 'MediabrowserUtility.closeDialog();'],
            0,
            0,
            'header'
        );

        $this->buttonList->add(
            'delete_folder',
            ['class' => 'delete no-display action-quaternary', 'label' => __('Delete Folder'), 'type' => 'button'],
            0,
            0,
            'header'
        );

        $this->buttonList->add(
            'delete_files',
            ['class' => 'delete no-display action-quaternary', 'label' => __('Delete Selected'), 'type' => 'button'],
            0,
            0,
            'header'
        );

        $this->buttonList->add(
            'new_folder',
            ['class' => 'save', 'label' => __('Create Folder'), 'type' => 'button'],
            0,
            0,
            'header'
        );

        $this->buttonList->add(
            'insert_files',
            ['class' => 'save no-display action-primary', 'label' => __('Add Selected'), 'type' => 'button'],
            0,
            0,
            'header'
        );
    }

    /**
     * Files action source URL
     *
     * @return string
     */
    public function getContentsUrl()
    {
        return $this->getUrl('cscmspage/*/contents', [
            'type' => $this->getRequest()->getParam('type'),
        ]);
    }

    /**
     * Javascript setup object for filebrowser instance
     *
     * @return string
     */
    public function getFilebrowserSetupObject()
    {
        $setupObject = new \Magento\Framework\DataObject();

        $setupObject->setData(
            [
                'newFolderPrompt' => __('New Folder Name:'),
                'deleteFolderConfirmationMessage' => __('Are you sure you want to delete this folder?'),
                'deleteFileConfirmationMessage' => __('Are you sure you want to delete this file?'),
                'targetElementId' => $this->getTargetElementId(),
                'contentsUrl' => $this->getContentsUrl(),
                'onInsertUrl' => $this->getOnInsertUrl(),
                'newFolderUrl' => $this->getNewfolderUrl(),
                'deleteFolderUrl' => $this->getDeletefolderUrl(),
                'deleteFilesUrl' => $this->getDeleteFilesUrl(),
                'headerText' => $this->getHeaderText(),
                'showBreadcrumbs' => true,
            ]
        );

        return $this->_jsonEncoder->encode($setupObject);
    }

    /**
     * New directory action target URL
     *
     * @return string
     */
    public function getNewfolderUrl()
    {
        return $this->getUrl('cscmspage/*/newFolder');
    }

    /**
     * Delete directory action target URL
     *
     * @return string
     */
    protected function getDeletefolderUrl()
    {
        return $this->getUrl('cscmspage/*/deleteFolder');
    }

    /**
     * Description goes here...
     *
     * @return string
     */
    public function getDeleteFilesUrl()
    {
        return $this->getUrl('cscmspage/*/deleteFiles');
    }

    /**
     * New directory action target URL
     *
     * @return string
     */
    public function getOnInsertUrl()
    {
        return $this->getUrl('cscmspage/*/onInsert');
    }

    /**
     * Target element ID getter
     *
     * @return string
     */
    public function getTargetElementId()
    {
        return $this->getRequest()->getParam('target_element_id');
    }
}
