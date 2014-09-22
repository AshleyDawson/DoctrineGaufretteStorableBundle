<?php

namespace AshleyDawson\DoctrineGaufretteStorableBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Class Configuration
 * @package AshleyDawson\DoctrineGaufretteStorableBundle\DependencyInjection
 *
 * @author Ashley Dawson <ashley@ashleydawson.co.uk>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ashley_dawson_doctrine_gaufrette_storable');

        // ...

        return $treeBuilder;
    }
}