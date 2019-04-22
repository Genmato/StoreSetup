<?php

namespace Genmato\StoreSetup\Model\Setup;

use Genmato\StoreSetup\Model\Config\Attribute;

use Magento\Customer\Model\Indexer\Address\AttributeProvider;

class AddressAttribute extends AbstractAttribute
{
    protected $identifier = 'address_attribute';
    protected $entityTypeCode = AttributeProvider::ENTITY;

    protected $attributeType = Attribute::TYPE_ADDRESS;

}
