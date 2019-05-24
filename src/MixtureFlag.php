<?php
namespace PHPSegmentTree;
/**
 * This is only an simple object used as a flag.
 *
 * @author fdrea
 *
 */
class MixtureFlag {
    private static $instance;
    public static function getInstance(): MixtureFlag {
        if (isset(self::$instance))
            return self::$instance;
        $instance = new self();
        self::$instance = $instance;
        return $instance;
    }
    private function __construct() {}
}