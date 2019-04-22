<?php
namespace Genmato\StoreSetup\Model\Config\Website;

use Genmato\StoreSetup\Model\Config\AbstractConverter;

class Converter extends AbstractConverter
{
    const NODE_XPATH = '/storesetup/websites/website';
    protected $addChildNodes = true;

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

        $websites = $xpath->evaluate(self::NODE_XPATH);
        /** @var $node \DOMNode */
        foreach ($websites as $node) {
            $output[] = $this->buildWebsites($node);
        }
        return $output;
    }

    /**
     * @param \DOMNode $node
     * @return array
     */
    protected function buildWebsites(\DOMNode $node)
    {
        $output = [];
        $output['code'] = $this->_getAttributeValue($node, 'code', '');
        $output['name'] = $this->_getAttributeValue($node, 'name', '');
        $output['sort_order'] = $this->_getAttributeValue($node, 'sort_order', 1);
        $output['is_default'] = $this->_getAttributeValue($node, 'default', 0);
        $output['previous'] = $this->_getAttributeValue($node, 'previous', '');

        if ($node->childNodes) {
            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                if ($childNode->nodeName === 'group') {
                    $output['groups'][] = $this->buildGroups($childNode);
                }
            }
        }
        return $output;
    }

    /**
     * @param \DOMNode $node
     * @return array
     */
    protected function buildGroups(\DOMNode $node)
    {
        $output = [];
        $output['name'] = $this->_getAttributeValue($node, 'name', '');
        $output['code'] = $this->_getAttributeValue($node, 'code', '');
        $output['catalog'] = $this->_getAttributeValue($node, 'catalog', 'Default Category');
        $output['previous'] = $this->_getAttributeValue($node, 'previous', '');

        if ($node->childNodes) {
            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                if ($childNode->nodeName === 'store') {
                    $output['stores'][] = $this->buildStores($childNode);
                }
            }
        }
        return $output;
    }

    /**
     * @param \DOMNode $node
     * @return array
     */
    protected function buildStores(\DOMNode $node)
    {
        $output = [];
        $output['code'] = $this->_getAttributeValue($node, 'code', '');
        $output['name'] = $this->_getAttributeValue($node, 'name', '');
        $output['status'] = $this->_getAttributeValue($node, 'status', 1);
        $output['is_default'] = $this->_getAttributeValue($node, 'default', 0);
        $output['sort_order'] = $this->_getAttributeValue($node, 'sort_order', 1);
        $output['is_active'] = $this->_getAttributeValue($node, 'active', 1);

        return $output;
    }
}
