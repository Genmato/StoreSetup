<?php

namespace Genmato\StoreSetup\Model\Setup\Attribute\Type;

use Exception as RootException;

class Exception extends RootException
{
    /**
     * Exception constructor.
     * @param string $message
     * @param int $code
     * @param Exception $previous
     * @internal param string $attribute
     */
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct(sprintf("Invalid Attribute Type Code: %s", $message), $code, $previous);
    }
}
