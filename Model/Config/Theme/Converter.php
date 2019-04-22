<?php
namespace Genmato\StoreSetup\Model\Config\Theme;

use Genmato\StoreSetup\Model\Config\AbstractConverter;

class Converter extends AbstractConverter
{
    const NODE_XPATH = '/storesetup/themes/theme';
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
            $output[] = $this->buildTheme($node);
        }
        return $output;
    }

    /**
     * @param \DOMNode $node
     * @return array
     */
    protected function buildTheme(\DOMNode $node)
    {
        $output = [];
        $output['name'] = $this->_getAttributeValue($node, 'name', '');
        if ($node->childNodes) {
            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                if ($childNode->nodeName === 'store') {
                    $output['stores'][] = $this->_getAttributeValue($childNode, 'id', '');
                } elseif ($childNode->nodeName === 'website') {
                    $output['websites'][] = $this->_getAttributeValue($childNode, 'id', '');
                }
            }
        }

        return $output;
    }
}
