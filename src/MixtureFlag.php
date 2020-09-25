<?php
namespace Swango\SegmentTree;
/**
 * This is only a simple object used as flag.
 */
final class MixtureFlag {
    private static MixtureFlag $instance;
    public static function getInstance(): MixtureFlag {
        if (isset(self::$instance)) {
            return self::$instance;
        }
        $instance = new self();
        self::$instance = $instance;
        return $instance;
    }
    private function __construct() {
    }
}