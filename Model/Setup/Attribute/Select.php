<?php

namespace Genmato\StoreSetup\Model\Setup\Attribute;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Config;
use Magento\Swatches\Model\Swatch;

class Select extends AbstractType
{
    protected $swatchType = Swatch::SWATCH_INPUT_TYPE_DROPDOWN;
    protected $frontend = 'select';
    protected $type = 'int';
    protected $scope = Attribute::SCOPE_GLOBAL;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * TextSwatch constructor.
     * @param Config $eavConfig
     */
    public function __construct(
        Config $eavConfig
    ) {
        parent::__construct();
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param $input
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function buildAttributeData($input)
    {
        $input[Swatch::SWATCH_INPUT_TYPE_KEY] = $this->swatchType;

        $attribute = $this->eavConfig->getAttribute('catalog_product', $input['attribute_code']);
        $options = $attribute->setStoreId(0)->getSource()->getAllOptions();

        // Find options already created for this attribute
        if (isset($input['option']['value']) && is_array($input['option']['value'])) {
            foreach ($input['option']['value'] as $optionId => $values) {
                if (is_numeric($optionId)) {
                    continue;
                }
                $origOptionId = false;
                foreach ($options as $sourceId => $option) {
                    if ($option['label'] === $values[0]) {
                        $foundOptionId = $sourceId;
                        $origOptionId = $option['value'];
                        break;
                    }
                }
                // Set original option Id to value array if already exists
                if ($origOptionId) {
                    $input['option']['converter'][$optionId] = $origOptionId;
                    $input['option']['value'][$origOptionId] = $values;
                    unset($input['option']['value'][$optionId]);
                    unset($options[$foundOptionId]);
                }
            }
        }

        // Delete options no longer needed
        foreach ($options as $delOption) {
            if ($delOption['value']) {
                $input['option']['delete'][$delOption['value']] = 1;
                if (!isset($input['option']['value'][$delOption['value']])) {
                    $input['option']['value'][$delOption['value']] = [];
                }
            }
        }

        // Set order field with same id's as main options
        if (isset($input['option']['order'])) {
            foreach ($input['option']['order'] as $optionId => $value) {
                if (isset($input['option']['converter'][$optionId])) {
                    $origOptionId = $input['option']['converter'][$optionId];
                    $input['option']['order'][$origOptionId] = $value;
                    unset($input['option']['order'][$optionId]);
                }
            }
        }

        // Set correct default value
        if (isset($input['option']['default'])) {
            foreach ($input['option']['default'] as $optionId => $value) {
                if ($value) {
                    if (isset($input['option']['converter'][$optionId])) {
                        $origOptionId = $input['option']['converter'][$optionId];
                        $input['option']['default'][$origOptionId] = $value;
                        unset($input['option']['default'][$optionId]);
                    } else {
                        $origOptionId = $optionId;
                    }
                    $input['option']['default'] = $origOptionId;
                    break;
                }
            }
        }

        return parent::buildAttributeData($input);
    }
}
