<?php

namespace Genmato\StoreSetup\Setup;

use Magento\Framework\Setup\ConsoleLogger as BaseConsoleLogger;

class ConsoleLogger extends BaseConsoleLogger
{

    /**
     * @param $message
     * @param array $data
     */
    public function logInfo($message, $data = [])
    {
        $this->console->writeln("<info>" . $this->buildMessage($message, $data) . '</info>');
    }

    /**
     * @param $message
     * @param array $data
     */
    public function logLine($message, $data = [])
    {
        $this->console->writeln("<detail>" . $this->buildMessage($message, $data) . '</detail>');
    }

    /**
     * @param $message
     * @param array $data
     */
    public function logException($message, $data = [])
    {
        $this->console->writeln("<error>" . $this->buildMessage($message, $data) . '</error>');
    }

    /**
     * @param $str
     * @param $args
     * @return string
     */
    protected function buildMessage($str, $args)
    {
        if (is_object($args)) {
            $args = get_object_vars($args);
        }
        $map = array_flip(array_keys($args));
        $new_str = preg_replace_callback(
            '/(^|[^%])%([a-zA-Z0-9_-]+)\$/',
            function ($m) use ($map) {
                return $m[1] . '%' . ($map[$m[2]] + 1) . '$';
            },
            $str
        );
        return vsprintf($new_str, $args);
    }
}
