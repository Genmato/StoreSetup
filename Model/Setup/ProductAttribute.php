<?php

namespace Genmato\StoreSetup\Model\Setup;

use Genmato\StoreSetup\Model\Config\Attribute;

use Magento\Catalog\Model\Product;

class ProductAttribute extends AbstractAttribute
{
    protected $identifier = 'product_attribute';
    protected $entityTypeCode = Product::ENTITY;

    protected $attributeType = Attribute::TYPE_PRODUCT;

}
