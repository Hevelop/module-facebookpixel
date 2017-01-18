<?php

namespace Hevelop\FacebookPixel\Model\Observer;

/**
 * FacebookPixel module observer
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class ControllerActionPostdispatch implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Hevelop\FacebookPixel\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

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
     * Fired by controller_action_postdispatch event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->sendCookieOnCartActionComplete($observer);
        //@TODO send send cookie on wishlist action
//        $this->sendCookieOnWishlistActionComplete($observer);
        //@TODO send send cookie on customer register success
//        $this->sendCookieOnCustomerRegisterSuccess($observer);

        return $this;
    }//end execute()

    /**
     * Send cookies after cart action
     *
     * @param Varien_Event_Observer $observer Magento observer object
     *
     * @return $this
     */
    public function sendCookieOnCartActionComplete(Varien_Event_Observer $observer)
    {
        if ($this->_helper->isEnabled() === false) {
            return $this;
        }

        $dataKeyAddToCart = \Hevelop\FacebookPixel\Helper\Data::COOKIE_CART_ADD;
        $dataKeyRemoveFromCart = \Hevelop\FacebookPixel\Helper\Data::COOKIE_CART_REMOVE;

        $productsToAdd = $this->_coreRegistry->registry('facebookpixel_products_addtocart');

        if (empty($productsToAdd) === false) {
            $this->_helper->setCookie(
                $dataKeyAddToCart,
                json_encode($productsToAdd),
                0,
                '/',
                null,
                null,
                false
            );
        }

        $productsToRemove = $this->_coreRegistry->registry('facebookpixel_products_to_remove');
        if (empty($productsToRemove) === false) {
            $this->_helper->setCookie(
                $dataKeyRemoveFromCart,
                rawurlencode(Mage::helper('core')->jsonEncode($productsToRemove)),
                0,
                '/',
                null,
                null,
                false
            );
        }

        return $this;

    }//end sendCookieOnCartActionComplete()

//    /**
//     * Send cookies after wishlist action
//     *
//     * @param Varien_Event_Observer $observer Magento observer object
//     *
//     * @return $this
//     */
//    public function sendCookieOnWishlistActionComplete(
//        Varien_Event_Observer $observer
//    ) {
//        if (Mage::helper('hevelop_facebookpixel')->isEnabled() === false) {
//            return $this;
//        }
//
//        $dataKeyAddToWishlist
//            = Hevelop_FacebookPixel_Helper_Data::COOKIE_WISHLIST_ADD;
//
//        $productsToAdd = Mage::registry('facebookpixel_products_addtowishlist');
//        if (empty($productsToAdd) === false) {
//            Mage::app()->getCookie()->set(
//                $dataKeyAddToWishlist,
//                rawurlencode(json_encode($productsToAdd)),
//                0,
//                '/',
//                null,
//                null,
//                false
//            );
//        }
//
//        return $this;
//
//    }//end sendCookieOnWishlistActionComplete()


//    /**
//     * Fired by customer_register_success event
//     *
//     * @param Varien_Event_Observer $observer Magento observer object
//     *
//     * @return $this
//     */
//    public function setFacebookPixelOnCustomerRegisterSuccess(
//        Varien_Event_Observer $observer
//    ) {
//        if (Mage::helper('hevelop_facebookpixel')->isEnabled() === false) {
//            return $this;
//        }
//
//        $customer = $observer->getCustomer();
//        Mage::unregister('facebookpixel_customer_registered');
//        Mage::register('facebookpixel_customer_registered', $customer);
//
//        return $this;
//
//    }//end setFacebookPixelOnCustomerRegisterSuccess()


}