<?php

namespace Hevelop\FacebookPixel\Model\Observer;

/**
 * FacebookPixel module observer
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class SalesQuoteLoadAfter implements \Magento\Framework\Event\ObserverInterface
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
     * SalesQuoteLoadAfter constructor.
     * @param \Hevelop\FacebookPixel\Helper\Data $helper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Hevelop\FacebookPixel\Helper\Data $helper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->_helper = $helper;
        $this->_coreRegistry = $coreRegistry;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Fired by sales_quote_product_add_after event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_helper->isModuleOutputEnabled() === false || !$this->_helper->isEnabled()) {
            return $this;
        }

        $quote = $observer->getEvent()->getQuote();
        $productQtys = array();
        foreach ($quote->getAllItems() as $quoteItem) {
            $parentQty = 1;
            switch ($quoteItem->getProductType()) {
                case 'bundle':
                case 'configurable':
                    break;
                case 'grouped':
                    $option = $quoteItem->getOptionByCode('product_type');
                    $id = $option->getProductId();
                    $id = $id . '-' . $quoteItem->getProductId();
                    $productQtys[$id] = $quoteItem->getQty();
                    break;
                case 'giftcard':
                    $id = $quoteItem->getId() . '-' . $quoteItem->getProductId();
                    $productQtys[$id] = $quoteItem->getQty();
                    break;
                default:
                    if ($quoteItem->getParentItem()) {
                        $parentQty = $quoteItem->getParentItem()->getQty();

                        $id = $quoteItem->getId() . '-';
                        $id .= $quoteItem->getParentItem()->getProductId() . '-';
                        $id .= $quoteItem->getProductId();
                    } else {
                        $id = $quoteItem->getProductId();
                    }

                    $productQtys[$id] = ($quoteItem->getQty() * $parentQty);
            }//end switch
        }//end foreach

        $dataKeyBeforeAddToCart
            = \Hevelop\FacebookPixel\Helper\Data::PRODUCT_QUANTITIES_BEFORE_ADDTOCART;

        if ($this->_checkoutSession->hasData(\Hevelop\FacebookPixel\Helper\Data::PRODUCT_QUANTITIES_BEFORE_ADDTOCART) === false
        ) {
            $this->_checkoutSession->setData(
                $dataKeyBeforeAddToCart,
                $productQtys
            );
        }

        return $this;
    }//end execute()
}