<?php

define('TESTS_TEMP_DIR', '/tmp');
define('VENDOR_PATH', realpath(__DIR__ . '/../vendor'));

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('AshleyDawson\DoctrineGaufretteStorableBundle\Tests', __DIR__);

Doctrine\Common\Annotations\AnnotationRegistry::registerFile(
    VENDOR_PATH.'/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
);

// todo: implement Mongo ODM support
//Doctrine\Common\Annotations\AnnotationRegistry::registerFile(
//    VENDOR_PATH.'/doctrine/mongodb-odm/lib/Doctrine/ODM/MongoDB/Mapping/Annotations/DoctrineAnnotations.php'
//);

$reader = new \Doctrine\Common\Annotations\AnnotationReader();
$reader = new \Doctrine\Common\Annotations\CachedReader($reader, new \Doctrine\Common\Cache\ArrayCache());
$_ENV['annotation_reader'] = $reader;