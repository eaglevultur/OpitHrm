<?php

namespace Opit\Notes\LeaveBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('opit_notes_leave');

        $rootNode
            ->children()
                ->arrayNode('leave_entitlement_plan')
                    ->canBeEnabled()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_days')
                            ->defaultValue('%leave_default_days%')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('calculation_params')
                    ->children()
                        ->scalarNode('calendar_days')
                            ->cannotBeEmpty()
                            ->defaultValue('365')
                        ->end()
                    ->end()
                ->end()
                ->integerNode('max_results')->cannotBeEmpty()->defaultValue('%max_results%')->end()
                ->integerNode('max_pager_pages')->cannotBeEmpty()->defaultValue('%max_pager_pages%')->end()
            ->end();

        return $treeBuilder;
    }
}
