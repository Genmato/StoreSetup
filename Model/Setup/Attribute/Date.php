<?php

namespace Genmato\StoreSetup\Model\Setup\Attribute;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

class Date extends AbstractType
{
    protected $frontend = 'date';
    protected $type = 'datetime';
    protected $backend = 'Magento\Eav\Model\Entity\Attribute\Backend\Datetime';
    protected $scope = Attribute::SCOPE_GLOBAL;
}