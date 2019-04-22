<?php

namespace Genmato\StoreSetup\Model\Setup\Attribute;

class Boolean extends AbstractType
{
    protected $frontend = 'boolean';
    protected $type = 'int';

    /**
     * @param $input
     * @return array
     */
    public function buildAttributeData($input)
    {
        unset($input['option']);

        return parent::buildAttributeData($input);
    }
}
