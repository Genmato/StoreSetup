<?php
namespace Genmato\StoreSetup\Model\Config\AttributeSet;

use Genmato\StoreSetup\Model\Config\AbstractConverter;
use Genmato\StoreSetup\Model\Config\Attribute;
use Genmato\StoreSetup\Model\Setup\Attribute\TypeList;

class Converter extends AbstractConverter
{
    const NODE_XPATH = '/storesetup/attributeSets/attributeSet';
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
    * @throws \InvalidArgumentException
    *
    * @SuppressWarnings(PHPMD.CyclomaticComplexity)
    */
    public function convert($source)
    {
        $xpath = new \DOMXPath($source);
        $output = [];

        $attributesets = $xpath->evaluate(self::NODE_XPATH);
        /** @var $node \DOMNode */
        foreach ($attributesets as $node) {
            $output[] = $this->buildAttributeSets($node);
        }
        return $output;
    }

    /**
     * @param \DOMNode $node
     * @return array
     */
    protected function buildAttributeSets(\DOMNode $node)
    {
        $output = [];
        $output['name'] = $this->_getAttributeValue($node, 'name', '');
        $output['copy_from'] = $this->_getAttributeValue($node, 'copyFrom', 'Default');
        $output['sort_order'] = $this->_getAttributeValue($node, 'sort_order', 1);
        $output['groups'] = [];

        if ($node->childNodes) {
            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                if ($childNode->nodeName === 'group') {
                    $output['groups'][] = $this->buildAttributeGroups($childNode);
                }
            }
        }
        return $output;
    }

    /**
     * @param \DOMNode $node
     * @return array
     */
    protected function buildAttributeGroups(\DOMNode $node)
    {
        $output = [];
        $output['attribute_group_code'] = $this->_getAttributeValue($node, 'id', '');
        $output['attribute_group_name'] = $this->_getAttributeValue($node, 'name', '');
        $output['sort_order'] = $this->_getAttributeValue($node, 'sort_order', 1);
        $output['attributes'] = [];

        if ($node->childNodes) {
            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                if ($childNode->nodeName === 'attribute') {
                    $output['attributes'][] = $this->buildAssignedAttributes($childNode);
                }
            }
        }
        return $output;
    }

    /**
     * @param \DOMNode $node
     * @return array
     */
    protected function buildAssignedAttributes(\DOMNode $node)
    {
        $output = [];
        $output['attribute_code'] = $this->_getAttributeValue($node, 'code', '');
        $output['sort_order'] = $this->_getAttributeValue($node, 'sort_order', '');

        return $output;
    }
}
