<?php

namespace Genmato\StoreSetup\Model\Setup\Attribute;

class Price extends AbstractType
{
    protected $frontend = 'text';
    protected $type = 'decimal';
    protected $backend = 'Magento\Catalog\Model\Product\Attribute\Backend\Price';
}