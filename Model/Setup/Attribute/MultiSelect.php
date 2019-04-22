<?php

namespace Genmato\StoreSetup\Model\Setup\Attribute;

class MultiSelect extends AbstractType
{
    protected $frontend = 'multiselect';
    protected $type = 'varchar';

    /**
     * @param $input
     * @return array
     */
    public function buildAttributeData($input)
    {
        return parent::buildAttributeData($input);
    }
}