<?php
/**
 * @category  Hevelop
 * @package   Hevelop_FacebookPixel
 * @author    Hevelop
 * @copyright Copyright (c) 2016 Hevelop (http://hevelop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License
 */

namespace Hevelop\FacebookPixel\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const COOKIE_CART_ADD = 'facebookpixel_cart_add';
    const COOKIE_CART_REMOVE = 'facebookpixel_cart_remove';
    const COOKIE_WISHLIST_ADD = 'facebookpixel_wishlist_add';
    const COOKIE_CUSTOMER_REGISTER = 'facebookpixel_customer_register';

    const XML_PATH_ENABLE = 'hevelop_facebookpixel/general/enabled';
    const XML_PATH_ATTRIBUTE_CODE = 'hevelop_facebookpixel/general/catalog_id_attribute_code';
    const XML_PATH_PRODUCT_CATALOG_ID = 'hevelop_facebookpixel/general/product_catalog_id';

    const PRODUCT_QUANTITIES_BEFORE_ADDTOCART = 'prev_product_qty';
    const PRODUCT_QUANTITIES_BEFORE_ADDTOWISHLIST = 'wishlist_prev_product_qty';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    public $moduleList;

    /**
     * Session config
     *
     * @var \Magento\Framework\Session\Config\ConfigInterface
     */
    protected $_sessionConfig;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
    )
    {
        $this->scopeConfig = $context->getScopeConfig();
        $this->moduleList = $moduleList;
        $this->_sessionConfig = $sessionConfig;
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;

        parent::__construct($context);
    }

    /**
     * Returns extension version.
     *
     * @return string
     */
    public function getExtensionVersion()
    {
        $moduleCode = 'Hevelop_FacebookPixel';
        $moduleInfo = $this->moduleList->getOne($moduleCode);
        return $moduleInfo['setup_version'];
    }

    /**
     * Based on provided configuration path returns configuration value.
     *
     * @param string $configPath
     * @return string
     */
    public function getConfig($configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Based on provided configuration path returns configuration value.
     *
     * @param string $configPath
     * @return string
     */
    public function isEnabled()
    {
        return $this->getConfig(self::XML_PATH_ENABLE);
    }

    /**
     * Add slashes to string and prepares string for javascript.
     *
     * @param string $str
     * @return string
     */
    public function escapeSingleQuotes($str)
    {
        return str_replace("'", "\'", $str);
    }

    /**
     * get configured attribute code to use as catalog product id
     *
     * @return string
     */
    public function getAttributeCodeForCatalog()
    {
        $attributeCode = $this->getConfig(self::XML_PATH_ATTRIBUTE_CODE);
        if (empty($attributeCode) === true) {
            $attributeCode = false;
        }
        return $attributeCode;
    }//end getAttributeCodeForCatalog()

    /**
     * If Facebook Pixel is enabled return product catalog id
     *
     * @return mixed|null|int
     */
    public function getProductCatalogId()
    {
        $productCatalogId = null;

        if ($this->isEnabled()) {
            $productCatalogId = $this->getConfig(self::XML_PATH_PRODUCT_CATALOG_ID);
        }

        return $productCatalogId;

    }//end getProductCatalogId()

    public function setCookie($name, $value, $duration = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        if (!$this->_sessionConfig->getUseCookies() || !$name) {
            return;
        }

        if (is_null($duration)) {
            $duration = $this->_sessionConfig->getCookieLifetime();
        }
        if (is_null($path)) {
            $path = $this->_sessionConfig->getCookiePath();
        }
        if (is_null($domain)) {
            $domain = $this->_sessionConfig->getCookieDomain();
        }
        if (is_null($secure)) {
            $secure = $this->_sessionConfig->getCookieSecure();
        }
        if (is_null($httponly)) {
            $httponly = $this->_sessionConfig->getCookieHttpOnly();
        }

        $metadata = $this->_cookieMetadataFactory->createPublicCookieMetadata();
        $metadata->setDuration($duration);
        $metadata->setPath($path);
        $metadata->setDomain($domain);
        $metadata->setSecure($secure);
        $metadata->setHttpOnly($httponly);
        $this->_cookieManager->setPublicCookie($this->getName(), $value, $metadata);
    }

}
