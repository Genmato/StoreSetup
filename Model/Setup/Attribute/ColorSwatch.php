<?php

namespace Genmato\StoreSetup\Model\Setup\Attribute;

use Magento\Swatches\Model\Swatch;

class ColorSwatch extends AbstractType
{
    protected $swatchType = Swatch::SWATCH_INPUT_TYPE_VISUAL;
    protected $swatchVisualType = Swatch::SWATCH_TYPE_VISUAL_COLOR;
    protected $frontend = 'select';
    protected $type = 'int';

    /**
     * @param $input
     * @return array
     */
    public function buildAttributeData($input)
    {
        $input[Swatch::SWATCH_INPUT_TYPE_KEY] = $this->swatchType;

        // Set order field with same id's as main options
        if (isset($input['swatch']['value'])) {
            foreach ($input['swatch']['value'] as $optionId => $value) {
                if (isset($input['option']['converter'][$optionId])) {
                    $origOptionId = $input['option']['converter'][$optionId];
                    $input['swatch']['value'][$origOptionId] = $value[1];
                    unset($input['swatch']['value'][$optionId]);
                } else {
                    $input['swatch']['value'][$optionId] = $value[1];
                }
            }
        }

        $input['optionvisual'] = $input['option'];
        $input['swatchvisual'] = $input['swatch'];
        $input['defaulttext'] = isset($input['default']) ? $input['default'] : [];

        return parent::buildAttributeData($input);
    }

    /**
     * Convert RGB code to hex value
     * If no RGB code is supplied, return white
     *
     * @param $color
     * @return string
     */
    protected function rgb2hex($color)
    {
        try {
            if ($color && (strpos($color, ',') !== false)) {
                list($colR, $colG, $colB) = explode(',', $color);
                return sprintf("#%02x%02x%02x", $colR, $colG, $colB);
            }
        } catch (Exception $ex) {
            return "#ffffff";
        }
    }
}