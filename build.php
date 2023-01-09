<?php

#https://odan.github.io/2017/08/16/create-a-php-phar-file.html
#https://blog.programster.org/creating-phar-files

// The php.ini setting phar.readonly must be set to 0
defined('AGENT_VERSION') || define('AGENT_VERSION', require __DIR__.DIRECTORY_SEPARATOR.'version.php');
$pharFile = AGENT_VERSION.'.phar';

chdir(__DIR__);

ini_set('phar.readonly', 'off');


// clean up
if (file_exists($pharFile)) {
    unlink($pharFile);
}
if (file_exists($pharFile . '.gz')) {
    unlink($pharFile . '.gz');
}

// create phar
$pharBuilder = new Phar($pharFile);


// creating our library using whole directory
$pharBuilder->buildFromDirectory('.');

// pointing main file which requires all classes
$pharBuilder->setDefaultStub('run.php', '/run.php');

// plus - compressing it into gzip
$pharBuilder->compress(Phar::GZ);

echo "$pharFile successfully created";
