<?php
namespace Genmato\StoreSetup\Model\Config\Category;

use Genmato\StoreSetup\Model\Config\AbstractConverter;

class Converter extends AbstractConverter
{
    const NODE_XPATH = '/storesetup/category/rootcatalog';
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

        $catalog = $xpath->evaluate(self::NODE_XPATH);
        /** @var $node \DOMNode */
        foreach ($catalog as $node) {
            $output[] = $this->buildCatalog($node);
        }
        return $output;
    }

    /**
     * @param \DOMNode $node
     * @param string $path
     * @return array
     */
    public function buildCatalog(\DOMNode $node, $path = '')
    {
        $output = [];
        $output['storesetup_id'] = $path . $this->_getAttributeValue($node, 'id', '');
        $output['url_key'] = $this->_getAttributeValue($node, 'url', '');
        $output['name'] = $this->_getAttributeValue($node, 'name', '');
        $output['is_active'] = $this->_getAttributeValue($node, 'active', 1);
        $output['include_in_menu'] = $this->_getAttributeValue($node, 'menu', 1);
        $output['is_anchor'] = $this->_getAttributeValue($node, 'anchor', 1);
        $output['position'] = $this->_getAttributeValue($node, 'position', 1);
        $output['store_data'][0] = [];

        foreach ($node->attributes as $name => $value) {
            if (!isset($output[$name]) && $name!='id') {
                $output[$name] = (string)$value->value;
            }
        }

        if ($node->childNodes && $this->addChildNodes) {
            /** @var \DOMNode $childNode */
            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                if ($childNode->nodeName === 'attribute') {
                    $code = $this->_getAttributeValue($childNode, 'code', '');
                    $store = $this->_getAttributeValue($childNode, 'store', 0);
                    if (!empty($code)) {
                        $output['store_data'][$store][$code] = $childNode->nodeValue;
                    }
                }
                if ($childNode->nodeName === 'catalog') {
                    $output['children'][] = $this->buildCatalog($childNode, $output['storesetup_id'] . '/');
                }
            }
        }
        return $output;
    }
}
