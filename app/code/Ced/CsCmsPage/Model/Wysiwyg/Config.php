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
 * @package     Ced_CsCmsPage
 * @author   CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsCmsPage\Model\Wysiwyg;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Ui\Component\Wysiwyg\ConfigInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class Config
 * @package Ced\CsCmsPage\Model\Wysiwyg
 */
class Config extends \Magento\Framework\DataObject implements ConfigInterface
{
    /**
     * Wysiwyg status enabled
     */
    const WYSIWYG_ENABLED = 'enabled';

    /**
     * Wysiwyg status configuration path
     */
    const WYSIWYG_STATUS_CONFIG_PATH = 'cms/wysiwyg/enabled';

    const WYSIWYG_SKIN_IMAGE_PLACEHOLDER_ID = 'Magento_Cms::images/wysiwyg_skin_image.png';

    /**
     * Wysiwyg status hidden
     */
    const WYSIWYG_HIDDEN = 'hidden';

    /**
     * Wysiwyg status disabled
     */
    const WYSIWYG_DISABLED = 'disabled';

    /**
     * Wysiwyg image directory
     */
    const IMAGE_DIRECTORY = 'wysiwyg';

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var array
     */
    protected $_windowSize;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Filesystem
     * @since 101.0.0
     */
    protected $filesystem;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\CompositeConfigProvider
     */
    private $configProvider;

    /**
     * Config constructor.
     * @param \Magento\Framework\UrlInterface $backendUrl
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param array $windowSize
     * @param array $data
     * @param \Magento\Cms\Model\Wysiwyg\CompositeConfigProvider|null $configProvider
     */
    public function __construct(
        \Magento\Framework\UrlInterface $backendUrl,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        array $windowSize = [],
        array $data = [],
        \Magento\Cms\Model\Wysiwyg\CompositeConfigProvider $configProvider = null
    )
    {
        $this->_backendUrl = $backendUrl;
        $this->_scopeConfig = $scopeConfig;
        $this->_assetRepo = $assetRepo;
        $this->_windowSize = $windowSize;
        $this->_storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->configProvider = $configProvider ?: ObjectManager::getInstance()
            ->get(\Ced\CsCmsPage\Model\Wysiwyg\CompositeConfigProvider ::class);
        parent::__construct($data);
    }

    /**
     * Return Wysiwyg config as \Magento\Framework\DataObject
     *
     * Config options description:
     *
     * enabled:                 Enabled Visual Editor or not
     * hidden:                  Show Visual Editor on page load or not
     * use_container:           Wrap Editor contents into div or not
     * no_display:              Hide Editor container or not (related to use_container)
     * translator:              Helper to translate phrases in lib
     * files_browser_*:         Files Browser (media, images) settings
     * encode_directives:       Encode template directives with JS or not
     *
     * @param array|\Magento\Framework\DataObject $data Object constructor params to override default config values
     * @return \Magento\Framework\DataObject
     */
    public function getConfig($data = [])
    {
        $config = new \Magento\Framework\DataObject();

        $config->setData(
            [
                'enabled' => $this->isEnabled(),
                'hidden' => $this->isHidden(),
                'baseStaticUrl' => $this->_assetRepo->getStaticViewFileContext()->getBaseUrl(),
                'baseStaticDefaultUrl' => str_replace('index.php/', '', $this->_backendUrl->getBaseUrl())
                    . $this->filesystem->getUri(DirectoryList::STATIC_VIEW) . '/',
                'directives_url' => $this->_backendUrl->getUrl('cscmspage/wysiwyg/directive'),
                'use_container' => false,
                'add_variables' => false,
                'add_widgets' => false,
                'no_display' => false,
                'add_directives' => true,
                'width' => '100%',
                'height' => '500px',
                'plugins' => [],
            ]
        );

        $config->setData('directives_url_quoted', preg_quote($config->getData('directives_url')));

        if (is_array($data)) {
            $config->addData($data);
        }

        $this->configProvider->processGalleryConfig($config);
        $config->addData(
            [
                'files_browser_window_width' => $this->_windowSize['width'],
                'files_browser_window_height' => $this->_windowSize['height'],
            ]
        );
        if ($config->getData('add_widgets')) {
            $this->configProvider->processWidgetConfig($config);
        }

        if ($config->getData('add_variables')) {
            $this->configProvider->processVariableConfig($config);
        }

        return $this->configProvider->processWysiwygConfig($config);
    }

    /**
     * Return path for skin images placeholder
     *
     * @return string
     */
    public function getSkinImagePlaceholderPath()
    {
        $staticPath = $this->_storeManager->getStore()->getBaseStaticDir();
        $placeholderPath = $this->_assetRepo->createAsset(self::WYSIWYG_SKIN_IMAGE_PLACEHOLDER_ID)->getPath();
        return $staticPath . '/' . $placeholderPath;
    }

    /**
     * Check whether Wysiwyg is enabled or not
     *
     * @return bool
     */
    public function isEnabled()
    {
        $wysiwygState = $this->_scopeConfig->getValue(
            self::WYSIWYG_STATUS_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return in_array($wysiwygState, [self::WYSIWYG_ENABLED, self::WYSIWYG_HIDDEN]);
    }

    /**
     * Check whether Wysiwyg is loaded on demand or not
     *
     * @return bool
     */
    public function isHidden()
    {
        $status = $this->_scopeConfig->getValue(
            self::WYSIWYG_STATUS_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $status == self::WYSIWYG_HIDDEN;
    }
}
