<?php

namespace Translation\PlatformAdapter\PhraseApp\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('translation_adapter_phrase_app');
        if (!method_exists($treeBuilder, 'getRootNode')) {
            $root = $treeBuilder->root('translation_adapter_phrase_app');
        } else {
            $root = $treeBuilder->getRootNode();
        }

        $root
            ->children()
                ->scalarNode('httplug_client')
                    ->defaultValue('httplug.client')->cannotBeEmpty()
                ->end()
                ->scalarNode('httplug_message_factory')
                    ->defaultValue('httplug.message_factory')->cannotBeEmpty()
                ->end()
                ->scalarNode('httplug_uri_factory')
                    ->defaultValue('httplug.uri_factory')->cannotBeEmpty()->end()
                ->scalarNode('project_id')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('token')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('default_locale')->end()
                ->arrayNode('locale_to_id_mapping')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('domains')
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
