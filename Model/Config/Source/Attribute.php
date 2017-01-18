<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Hevelop\FacebookPixel\Model\Config\Source;

class Attribute implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    private $productAttributeCollectionFactory;

    /** @var  array */
    private $_options;

    /**
     * Attribute constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $productAttributeCollectionFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $productAttributeCollectionFactory
    )
    {
        $this->productAttributeCollectionFactory = $productAttributeCollectionFactory;
    }

    /**
     * get all catalog attributes
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (is_array($this->_options) === false) {
            $attributes = $this->productAttributeCollectionFactory->create()->getItems();
            $this->_options[]
                = array(
                'label' => __('Default product ID'),
                'value' => '',
            );
            foreach ($attributes as $attribute) {
                $this->_options[]
                    = array(
                    'label' => $attribute->getFrontendLabel(),
                    'value' => $attribute->getAttributeCode(),
                );
            }//end foreach
        }
        return $this->_options;
    }//end getAllOptions()

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
