<?php
/**
 * PimcoreRecaptchaBundle
 * Copyright (c) Lukaschel
 */

namespace Lukaschel\PimcoreRecaptchaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('pimcore_recaptcha');
        $rootNode
            ->children()
                ->scalarNode('storage_path')->cannotBeEmpty()->defaultValue('/PimcoreRecaptcha')->end()
            ->end();

        return $treeBuilder;
    }
}
