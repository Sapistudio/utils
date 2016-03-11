<?php
/**
 * Utilitário de rastreamento de chamada 
 *
 * @codingStandard PSR2
 * @author J. Augusto <augustowebd@gmail.com>
 * @version 0.0.1
 * */
final class Who
{
    private function __construct()
    {
    }

    /**
     * @return stdClass
     * */
    public static function callme()
    {
        return self::parser(current(self::tracker()));
    }

    /**
     * @return stdClass
     * */
    public static function followme()
    {
        $return  =
        $swap    = null;
        $tracker = self::tracker();
        array_pop($tracker);

        for ($i = 0, $len = count($tracker); $i < $len;) {
            $iNext  = $i + 1;
            $result =  self::parser($tracker[$i]);

            if (null === $swap) {
                $return =
                $swap   = &$result;
            }

            $swap->href = null;

            if (isset($tracker[$iNext])) {
                $swap->href = self::parser($tracker[$iNext]);
                $swap = &$swap->href;
            }

            $i = $iNext;
        }

        return $return;
    }

    private static function tracker()
    {
        if (function_exists('debug_backtrace')) {
            return  array_reverse(debug_backtrace());
        }

        $eTracker = new \Exception;
        return  array_reverse($eTracker->getTrace());
    }

    private static function parser($stack)
    {
        $return         = new \stdClass;
        $return->source = $stack['file'];
        $return->caller = $stack['function'] === '{closure}' ? '?' : $stack['function'];
        $return->type   = (isset($stack['class']) ? 'method' : 'function');
        $return->line   = $stack['line'];

        if (isset($stack['class'])) {
            $return->caller = $stack['class'] . $stack['type'] . $return->caller;
        }

        if (count($stack['args'])) {

            foreach ($stack['args'] as $pos => $val) {
                $return->args[$pos]        = new \stdClass;
                $return->args[$pos]->pos   = $pos;
                $return->args[$pos]->type  = gettype($val);
                $return->args[$pos]->value = $val;
            }
        }

        if ('call' == $return->caller) {
            $return->caller =
            $return->type   = null;
        }

        return $return;
    }
}
