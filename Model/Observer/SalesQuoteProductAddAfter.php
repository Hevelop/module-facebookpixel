<?php

namespace Hevelop\FacebookPixel\Model\Observer;

/**
 * FacebookPixel module observer
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class SalesQuoteProductAddAfter implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Hevelop\FacebookPixel\Helper\Data
     */
    private $_helper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $_store;

    /**
     * SalesQuoteProductAddAfter constructor.
     * @param \Hevelop\FacebookPixel\Helper\Data $helper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\Store $store
     */
    public function __construct(
        \Hevelop\FacebookPixel\Helper\Data $helper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\Store $store
    )
    {
        $this->_helper = $helper;
        $this->_coreRegistry = $coreRegistry;
        $this->_checkoutSession = $checkoutSession;
        $this->_store = $store;
    }

    /**
     * Fired by sales_quote_product_add_after event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_helper->isModuleOutputEnabled() === false || $this->_helper->isEnabled() == false) {
            return $this;
        }
        $dataKeyBeforeAddToCart
            = \Hevelop\FacebookPixel\Helper\Data::PRODUCT_QUANTITIES_BEFORE_ADDTOCART;
        $products = $this->_coreRegistry->registry('facebookpixel_products_addtocart');
        if (is_array($products) === false) {
            $products = array();
        }
        $lastValues = array();
        if ($this->_checkoutSession->hasData($dataKeyBeforeAddToCart) === true) {
            $lastValues = $this->_checkoutSession->getData($dataKeyBeforeAddToCart);
        }
        $items = $observer->getEvent()->getItems();
        foreach ($items as $quoteItem) {
            $product = $this->getProductFromItem($quoteItem, $lastValues);
            if ($product !== false) {
                $products[] = $product;
            }
        }//end foreach
        $this->_coreRegistry->unregister('facebookpixel_products_addtocart');
        $this->_coreRegistry->register('facebookpixel_products_addtocart', $products);
        $this->_checkoutSession->unsetData($dataKeyBeforeAddToCart);

        return $this;
    }//end execute()


    /**
     * Returns product info from a given item (quote or wishlist)
     *
     * @param mixed $item       item to get product from
     * @param array $lastValues reference to parent code
     *
     * @return mixed $product
     */
    protected function getProductFromItem($item, $lastValues)
    {
        $product          = false;
        $id               = $item->getProductId();
        $parentQty        = 1;
        $price            = $item->getProduct()->getPrice();
        $baseCurrencyCode = $this->_store->getBaseCurrencyCode();
        $productCatalogId = $this->_helper->getProductCatalogId();
        $attributeCode    = $this->_helper->getAttributeCodeForCatalog();

        switch ($item->getProductType()) {
            case 'configurable':
            case 'bundle':
                break;
            case 'grouped':
                $id  = $item->getOptionByCode('product_type')->getProductId().'-';
                $id .= $item->getProductId();
            // no break;
            default:

                if ($attributeCode === false) {
                    $productId = $item->getProduct()->getId();
                } else {
                    $productId = $item->getProduct()->getData($attributeCode);
                }

                if ($item->getParentItem()) {
                    $parentQty = $item->getParentItem()->getQty();
                    $id        = $item->getId().'-';
                    $id       .= $item->getParentItem()->getProductId().'-';
                    $id       .= $item->getProductId();

                    if ($attributeCode === false) {
                        $productId = $item->getParentItem()->getProduct()->getId();
                    } else {
                        $productId = $item->getParentItem()->getProduct()->getData(
                            $attributeCode
                        );
                    }

                    $parentProductType = $item->getParentItem()->getProductType();
                    if ($parentProductType === 'configurable') {
                        $price = $item->getParentItem()->getProduct()->getPrice();
                    }
                }
                if ($item->getProductType() === 'giftcard') {
                    $price = $item->getProduct()->getFinalPrice();
                }

                $check  = array_key_exists($id, $lastValues) === true;
                $oldQty = ($check === true) ? $lastValues[$id] : 0;

                $finalQty = (($parentQty * $item->getQty()) - $oldQty);
                if ($finalQty !== 0) {



                    $product = array(
                        'id'                 => $productId,
                        'sku'                => $item->getSku(),
                        'name'               => $item->getName(),
                        'price'              => $price,
                        'qty'                => $finalQty,
                        'currency'           => $baseCurrencyCode,
                        'product_catalog_id' => $productCatalogId,
                    );
                }//end if
        }//end switch

        return $product;

    }//end getProductFromItem()
}