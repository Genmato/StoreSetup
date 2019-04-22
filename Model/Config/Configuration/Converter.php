<?php
namespace Genmato\StoreSetup\Model\Config\Configuration;

use Genmato\StoreSetup\Model\Config\AbstractConverter;

class Converter extends AbstractConverter
{
    const NODE_XPATH = '/storesetup/configuration/config';
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

        $themes = $xpath->evaluate(self::NODE_XPATH);
        /** @var $node \DOMNode */
        foreach ($themes as $node) {
            $output[] = $this->buildConfiguration($node);
        }
        return $output;
    }

    /**
     * @param \DOMNode $node
     * @return array
     */
    protected function buildConfiguration(\DOMNode $node)
    {
        $output = [];
        $output['id'] = $this->_getAttributeValue($node, 'id', '');
        $output['path'] = $this->_getAttributeValue($node, 'path', '');
        $output['value'] = $this->_getAttributeValue($node, 'value', '');
        $output['scope'] = $this->_getAttributeValue($node, 'scope', 'default');
        $output['scope_id'] = $this->_getAttributeValue($node, 'scope_id', 'admin');
        $output['environment'] = [];
        if ($node->childNodes) {
            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                if ($childNode->nodeName === 'environment') {
                    $environment = $this->_getAttributeValue($childNode, 'name', '');
                    $value = $this->_getAttributeValue($childNode, 'value', '');
                    $output['environment'][$environment] = $value;
                }
            }
        }

        return $output;
    }
}
