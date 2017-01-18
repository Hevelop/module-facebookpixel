<?php
/**
 * @category  Hevelop
 * @package   Hevelop_FacebookPixel
 * @author    Hevelop
 * @copyright Copyright (c) 2016 Hevelop (http://hevelop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License
 */

namespace Hevelop\FacebookPixel\Block;

class Code extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Hevelop\FacebookPixel\Helper\Data
     */
    public $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    public $catalogHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection\
     */
    private $_productCollection;

    /**
     * @var boolean
     */
    private $_showCrossSells;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Hevelop\FacebookPixel\Helper\Data $helper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Catalog\Helper\Data $catalogHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Hevelop\FacebookPixel\Helper\Data $helper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Helper\Data $catalogHelper,
        array $data = []
    )
    {
        $this->storeManager = $context->getStoreManager();
        $this->helper = $helper;
        $this->coreRegistry = $coreRegistry;
        $this->catalogHelper = $catalogHelper;
        parent::__construct($context, $data);
    }

    /**
     * Used in .phtml file and returns array of data.
     *
     * @return array
     */
    public function getFacebookPixelData()
    {
        $data = [];

        $data['id'] = $this->helper
            ->getConfig('hevelop_facebookpixel/general/pixel_id');

        $data['product_catalog_id'] = $this->helper
            ->getConfig('hevelop_facebookpixel/general/product_catalog_id');

        $data['full_action_name'] = $this->getRequest()->getFullActionName();

        return $data;
    }

    /**
     * Returns product data needed for dynamic ads tracking.
     *
     * @return array
     */
    public function getProductData()
    {
        $p = $this->coreRegistry->registry('current_product');

        $data = [];

        $data['content_name'] = $this->helper
            ->escapeSingleQuotes($p->getName());
        $data['content_ids'] = $this->helper
            ->escapeSingleQuotes($p->getSku());
        $data['content_type'] = 'product';
        $data['value'] = number_format(
            $this->getCalculatedPrice(),
            2,
            '.',
            ''
        );
        $data['currency'] = $this->getCurrencyCode();

        return $data;
    }

    /**
     * Returns product data needed for dynamic ads tracking.
     *
     * @return array
     */
    public function getCategoryData()
    {
        $data = [];

        $attributeCode = $this->helper->getAttributeCodeForCatalog();

        $currCat = $this->getCurrentCategory();


        if ($currCat && !$this->coreRegistry->registry('product')) {
            $products = $this->getProducts();
            $productIds = array();
            foreach ($products as $product) {
                if ($attributeCode === false) {
                    $productIds[] = $product->getId();
                } else {
                    $productIds[] = $product->getData($attributeCode);
                }
            }//end foreach

            $data['content_category'] = $this->helper
                ->escapeSingleQuotes($currCat->getName());

            if (count($productIds) > 0) {
                $data['content_ids'] = implode("','", $productIds);
                $data['content_type'] = 'product';
            }
        }

        return $data;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Catalog_Model_Resource_Collection_Abstract | null
     */
    protected function getProducts()
    {
        /** @var Mage_Catalog_Model_Category $category */
        $category = $this->getCurrentCategory();
        if ($category && ($category->getDisplayMode() == \Magento\Catalog\Model\Category::DM_MIXED ||
                $category->getDisplayMode() == \Magento\Catalog\Model\Category::DM_PRODUCT)
        ) {
            return $this->getProductCollection();
        }
        return null;
    }//end getProducts()


    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Catalog_Model_Resource_Collection_Abstract | null
     */
    protected function getProductCollection()
    {
        /* For catalog list and search results
         * Expects getListBlock as Mage_Catalog_Block_Product_List
         */
        if (is_null($this->_productCollection)) {
            $this->_productCollection = $this->getListBlock()->getLoadedProductCollection();
        }
        /* For collections of cross/up-sells and related
         * Expects getListBlock as one of the following:
         * Enterprise_TargetRule_Block_Catalog_Product_List_Upsell | _linkCollection
         * Enterprise_TargetRule_Block_Catalog_Product_List_Related | _items
         * Enterprise_TargetRule_Block_Checkout_Cart_Crosssell | _items
         * Mage_Catalog_Block_Product_List_Related | _itemCollection
         * Mage_Catalog_Block_Product_List_Upsell | _itemCollection
         * Mage_Checkout_Block_Cart_Crosssell, | setter items
         */
        if ($this->_showCrossSells && is_null($this->_productCollection)) {
            $this->_productCollection = $this->getListBlock()->getItemCollection();
        }
        // Support for CE
        if (is_null($this->_productCollection)
            && ($this->getBlockName() == 'catalog.product.related'
                || $this->getBlockName() == 'checkout.cart.crosssell')
        ) {
            $this->_productCollection = $this->getListBlock()->getItems();
        }
        //limit collection for page product
        $this->_productCollection->setCurPage($this->getCurrentPage());
        // we need to set pagination only if passed value integer and more that 0
        $limit = (int)$this->getListBlock()->getToolbarBlock()->getLimit();
        if ($limit) {
            $this->_productCollection->setPageSize($limit);
        }
        return $this->_productCollection;
    }//end getProductCollection()

    /**
     * getListBlock
     *
     * @return mixed
     */
    public function getListBlock()
    {
        return $this->getLayout()->getBlock($this->getData('block_name'));
    }//end getListBlock()


    /**
     * Retrieves a current category
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCurrentCategory()
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = null;
        /** @var \Magento\Catalog\Model\Layer $catalogLayer */
        if ($catalogLayer = $this->coreRegistry->registry('catalog_layer')) {
            $category = $catalogLayer->getCurrentCategory();
        } else if ($this->coreRegistry->registry('current_category')) {
            $category = $this->coreRegistry->registry('current_category');
        }
        return $category;
    }//end getCurrentCategory()

    /**
     * Returns product calculated price depending option selected in
     * Stores > Cofiguration > Sales > Tax > Price Display Settings
     * If "Excluding Tax" is selected price will not include tax.
     * If "Including Tax" or "Including and Excluding Tax" is selected price
     * will include tax.
     *
     * @return int|float|string
     */
    public function getCalculatedPrice()
    {
        $p = $this->coreRegistry->registry('current_product');

        $productType = $p->getTypeId();

        $calculatedPrice = 0;

        // Tax Display
        // 1 - excluding tax
        // 2 - including tax
        // 3 - including and excluding tax
        $tax = (int)$this->helper->getConfig('tax/display/type');

        if ($productType == 'configurable') {
            if ($tax === 1) {
                $calculatedPrice = $p->getFinalPrice();
            } else {
                $calculatedPrice = $this->catalogHelper->getTaxPrice(
                    $p,
                    $p->getFinalPrice(),
                    true,
                    null,
                    null,
                    null,
                    $this->storeManager->getStore()->getId(),
                    true,
                    false
                );
            }
        } elseif ($productType == 'grouped') {
            $associatedProducts = $p->getTypeInstance(true)
                ->getAssociatedProducts($p);

            $prices = [];

            foreach ($associatedProducts as $associatedProduct) {
                $prices[] = $associatedProduct->getPrice();
            }

            if (!empty($prices)) {
                $calculatedPrice = min($prices);
            }

            // downloadable, simple, virtual
        } else {
            if ($tax === 1) {
                $calculatedPrice = $p->getFinalPrice();
            } else {
                $calculatedPrice = $this->catalogHelper->getTaxPrice(
                    $p,
                    $p->getFinalPrice(),
                    true,
                    null,
                    null,
                    null,
                    $this->storeManager->getStore()->getId(),
                    false,
                    false
                );
            }
        }

        return $calculatedPrice;
    }

    /**
     * Returns 3 letter currency code like USD, GBP, EUR, etc.
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return strtoupper(
            $this->storeManager->getStore()->getCurrentCurrency()->getCode()
        );
    }
}
