<?php

namespace Genmato\StoreSetup\Model\Setup\Attribute;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Swatches\Model\Swatch;

class TextSwatch extends Select
{
    protected $swatchType = Swatch::SWATCH_INPUT_TYPE_TEXT;
    protected $frontend = 'select';
    protected $type = 'int';
    protected $scope = Attribute::SCOPE_GLOBAL;

    /**
     * @param $input
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function buildAttributeData($input)
    {
        $input = parent::buildAttributeData($input);

        // Set order field with same id's as main options
        if (isset($input['swatch']['value'])) {
            foreach ($input['swatch']['value'] as $optionId => $value) {
                if (isset($input['option']['converter'][$optionId])) {
                    $origOptionId = $input['option']['converter'][$optionId];
                    $input['swatch']['value'][$origOptionId] = $value[1];
                    unset($input['swatch']['value'][$optionId]);
                }
            }
        }

        $input['optiontext'] = $input['option'];
        $input['swatchtext'] = $input['swatch'];
        $input['defaulttext'] = isset($input['default']) ? $input['default'] : [];

        return $input;
    }
}
