<?php

namespace Genmato\StoreSetup\Model\Config;

use Magento\Framework\Config\ConverterInterface;

abstract class AbstractConverter implements ConverterInterface
{
    protected $addChildNodes = true;

    /**
     * Get attribute value
     *
     * @param \DOMNode $input
     * @param string $attributeName
     * @param string|null $default
     * @return null|string
     */
    protected function _getAttributeValue(\DOMNode $input, $attributeName, $default = null)
    {
        $node = $input->attributes->getNamedItem($attributeName);
        return ($node && !($node === null)) ? $node->nodeValue : $default;
    }
}
