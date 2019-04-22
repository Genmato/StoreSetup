<?php

namespace Genmato\StoreSetup\Model\Setup;

use Genmato\StoreSetup\Model\Config\Attribute;

use Magento\Catalog\Model\Category as CategoryAlias;

class CategoryAttribute extends AbstractAttribute
{
    protected $identifier = 'category_attribute';
    protected $entityTypeCode = CategoryAlias::ENTITY;

    protected $attributeType = Attribute::TYPE_CATEGORY;
}
