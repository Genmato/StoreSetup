<?php

namespace Genmato\StoreSetup\Model\Setup;

use Genmato\StoreSetup\Model\Config\Attribute;

use Magento\Customer\Model\Customer;

class CustomerAttribute extends AbstractAttribute
{
    protected $identifier = 'customer_attribute';
    protected $entityTypeCode = Customer::ENTITY;

    protected $attributeType = Attribute::TYPE_CUSTOMER;

}
