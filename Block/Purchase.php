<?php
/**
 * @category  Hevelop
 * @package   Hevelop_FacebookPixel
 * @author    Hevelop
 * @copyright Copyright (c) 2016 Hevelop (http://hevelop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License
 */

namespace Hevelop\FacebookPixel\Block;

class Purchase extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Hevelop\FacebookPixel\Helper\Data
     */
    public $helper;
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    public $checkoutSession;
    
    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Hevelop\FacebookPixel\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Hevelop\FacebookPixel\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        $this->helper          = $helper;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
    }
    
    /**
     * Returns data needed for purchase tracking.
     *
     * @return array|null
     */
    public function getOrderData()
    {
        $order   = $this->checkoutSession->getLastRealOrder();
        $orderId = $order->getIncrementId();
        
        if ($orderId) {
            $items = [];
    
            foreach ($order->getAllVisibleItems() as $item) {
                $items[] = [
                    'name' => $item->getName(), 'sku' => $item->getSku()
                ];
            }
    
            $data = [];
    
            if (count($items) === 1) {
                $data['content_name'] = $this->helper
                    ->escapeSingleQuotes($items[0]['name']);
            }
    
            $ids = '';
            foreach ($items as $i) {
                $ids .= "'" . $this->helper
                    ->escapeSingleQuotes($i['sku']) . "', ";
            }
    
            $data['content_ids']  = trim($ids, ", ");
            $data['content_type'] = 'product';
            $data['value']        = number_format(
                $order->getGrandTotal(),
                2,
                '.',
                ''
            );
            $data['currency']     = $order->getOrderCurrencyCode();
    
            return $data;
        } else {
            return null;
        }
    }
}
