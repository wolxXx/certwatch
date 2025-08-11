<?php

namespace Certwatch\Test;


error_reporting(error_level: E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
if (false === defined(constant_name: 'IS_IN_TEST_ENV')) {
    define(constant_name: 'IS_IN_TEST_ENV', value: true);
}
require_once __DIR__ . '/TestBase.php';
ini_set(option: 'xdebug.var_display_max_data', value: '1000000');
ini_set(option: 'xdebug.var_display_max_children', value: '1000000');
ini_set(option: 'xdebug.var_display_max_depth', value: '1000000');

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
        if (file_exists(filename: $vendorPath . '/autoload.php')) {
            require $vendorPath . '/autoload.php';
        }
    }


    protected static function findParentPath($path)
    {
        $dir         = __DIR__;
        $previousDir = '.';
        while (!is_dir(filename: $dir . '/' . $path)) {
            $dir = dirname(path: $dir);
            if ($previousDir === $dir) {
                return false;
            }
            $previousDir = $dir;
        }

        return $dir . '/' . $path;
    }


    public static function chroot()
    {
        chdir(directory: __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
    }
}

Bootstrap::init();
Bootstrap::chroot();