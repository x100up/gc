<?php
mb_internal_encoding('UTF-8');

/** @var $loader \Composer\Autoload\ClassLoader   */
$loader = include __DIR__.'/../../vendor/autoload.php';

$loader->add('', [
	__DIR__,
	__DIR__.'/../../src',
	__DIR__.'/../../',
	__DIR__.'/../../src/Harpoon',
]);