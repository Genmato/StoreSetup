<?php
namespace Genmato\StoreSetup\Model\Config\Attribute;

use Genmato\StoreSetup\Model\Config\AbstractConverter;
use Genmato\StoreSetup\Model\Config\Attribute;
use Genmato\StoreSetup\Model\Setup\Attribute\TypeList;
use Genmato\StoreSetup\Model\Setup\Attribute\Type\Exception as TypeException;

class Converter extends AbstractConverter
{
    const PRODUCT_NODE_XPATH = '/storesetup/product/attribute';
    const CATEGORY_NODE_XPATH = '/storesetup/category/attribute';
    const CUSTOMER_NODE_XPATH = '/storesetup/customer/attribute';
    const ADDRESS_NODE_XPATH = '/storesetup/address/attribute';
    /**
     * @var TypeList
     */
    protected $typeList;

    /**
     * @param TypeList $typeList
     */
    public function __construct(
        TypeList $typeList
    ) {
        $this->typeList = $typeList;
    }

    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     * @throws TypeException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function convert($source)
    {
        $xpath = new \DOMXPath($source);
        $output = [];

        $attributes = $xpath->evaluate(self::PRODUCT_NODE_XPATH);
        /** @var $node \DOMNode */
        foreach ($attributes as $node) {
            $output[Attribute::TYPE_PRODUCT][] = $this->buildAttribute($node);
        }

        $attributes = $xpath->evaluate(self::CATEGORY_NODE_XPATH);
        /** @var $node \DOMNode */
        foreach ($attributes as $node) {
            $output[Attribute::TYPE_CATEGORY][] = $this->buildAttribute($node);
        }

        $attributes = $xpath->evaluate(self::CUSTOMER_NODE_XPATH);
        /** @var $node \DOMNode */
        foreach ($attributes as $node) {
            $output[Attribute::TYPE_CUSTOMER][] = $this->buildAttribute($node);
        }

        $attributes = $xpath->evaluate(self::ADDRESS_NODE_XPATH);
        /** @var $node \DOMNode */
        foreach ($attributes as $node) {
            $output[Attribute::TYPE_ADDRESS][] = $this->buildAttribute($node);
        }
        return $output;
    }

    /**
     * @param \DOMNode $node
     * @return mixed
     * @throws TypeException
     */
    protected function buildAttribute(\DOMNode $node)
    {
        $type = $this->_getAttributeValue($node, 'type', 'text');
        $processor = $this->typeList->getType($type);
        if (!$processor) {
            throw new TypeException($type);
        }
        $output = [];
        $output['attribute_code'] = $this->_getAttributeValue($node, 'code', '');
        $output['frontend_label'][0] = $this->_getAttributeValue($node, 'label', ''); // Default Text
        $output['frontend_label'][1] = $this->_getAttributeValue($node, 'label', ''); // Admin store value

        foreach ($node->attributes as $name => $value) {
            if (!isset($output[$name])) {
                $output[$name] = (string)$value->value;
            }
        }
        unset($output['code']);
        unset($output['type']);

        if ($node->childNodes) {
            $output['option'] = [];

            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                if ($childNode->nodeName === 'label') {
                    $store = $this->_getAttributeValue($childNode, 'store', '');
                    if (isset($this->stores[$store])) {
                        $output['frontend_label'][$this->stores[$store]->getId()] = (string)$childNode->nodeValue;
                    }
                } elseif ($childNode->nodeName === 'values') {
                    foreach ($childNode->childNodes as $valueChildNode) {
                        if ($valueChildNode->nodeType != XML_ELEMENT_NODE) {
                            continue;
                        }
                        $output = $this->buildAttributeValues($valueChildNode, $output);
                    }
                }
            }
        }

        return $processor->buildAttributeData($output);
    }

    /**
     * @param \DOMNode $node
     * @param $output
     * @return mixed
     */
    protected function buildAttributeValues(\DOMNode $node, $output)
    {
        $optionId = 'option_' . $this->_getAttributeValue($node, 'key');

        $output['option']['value'][$optionId][0] = $this->_getAttributeValue($node, 'key');
        $output['option']['value'][$optionId][1] = $this->_getAttributeValue($node, 'label');
        $output['option']['order'][$optionId] = $this->_getAttributeValue($node, 'order', 1);
        $output['option']['default'][$optionId] = $this->_getAttributeValue($node, 'default', 0);

        if ($this->_getAttributeValue($node, 'swatch', false)) {
            $output['swatch']['value'][$optionId][1] = $this->_getAttributeValue($node, 'swatch');
        }

        if ($node->childNodes) {
            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                if ($childNode->nodeName === 'label') {
                    $store = $this->_getAttributeValue($childNode, 'store', '');
                    if (isset($this->stores[$store])) {
                        $storeId = $this->stores[$store]->getId();
                        $output['option']['value'][$optionId][$storeId] = (string)$childNode->nodeValue;

                        if ($this->_getAttributeValue($childNode, 'swatch', false)) {
                            $output['swatch']['value'][$optionId][$storeId] = $this->_getAttributeValue($childNode, 'swatch');
                        }
                    }
                }
            }
        }
        return $output;
    }
}
