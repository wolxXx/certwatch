<?php

namespace Certwatch\Test;


error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
if (false === defined('IS_IN_TEST_ENV')) {
    define('IS_IN_TEST_ENV', true);
}
require_once __DIR__ . '/TestBase.php';
ini_alter('xdebug.var_display_max_data', '1000000');
ini_alter('xdebug.var_display_max_children', '1000000');
ini_alter('xdebug.var_display_max_depth', '1000000');

/**
 * Test bootstrap, for setting up autoloading
 */
class Bootstrap
{

    public static function init()
    {
        static::initAutoloader();
    }


    protected static function initAutoloader()
    {
        $vendorPath = static::findParentPath('vendor');
        if (file_exists($vendorPath . '/autoload.php')) {
            require $vendorPath . '/autoload.php';
        }
    }


    protected static function findParentPath($path)
    {
        $dir         = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) {
                return false;
            }
            $previousDir = $dir;
        }

        return $dir . '/' . $path;
    }


    public static function chroot()
    {
        chdir(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
    }
}

Bootstrap::init();
Bootstrap::chroot();