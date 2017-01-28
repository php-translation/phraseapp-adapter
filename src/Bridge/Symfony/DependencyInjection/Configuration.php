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
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('translation_adapter_phrase_app');

        $root->children()
            ->scalarNode('httplug_client')->defaultValue('httplug.client')->cannotBeEmpty()->end()
            ->scalarNode('httplug_message_factory')->defaultValue('httplug.message_factory')->cannotBeEmpty()->end()
            ->scalarNode('httplug_uri_factory')->defaultValue('httplug.uri_factory')->cannotBeEmpty()->end()
            ->scalarNode('project_id')->cannotBeEmpty()->end()
            ->scalarNode('token')->cannotBeEmpty()->end()
            ->arrayNode('locale_to_id_mapping')->prototype('scalar')->end()->end()
        ->end();

        return $treeBuilder;
    }
}
