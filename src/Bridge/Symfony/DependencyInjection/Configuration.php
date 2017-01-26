<?php

namespace Translation\PlatformAdapter\PhraseApp\Bridge\Symfony\DependencyInjection;

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
        $root = $treeBuilder->root('translation_adapter_phrase_app');

        $root->children()
            ->scalarNode('project_id')->cannotBeEmpty()->end()
            ->scalarNode('token')->cannotBeEmpty()->end()
            ->arrayNode('locale_to_id_mapping')->prototype('scalar')->end()->end()
        ->end();

        return $treeBuilder;
    }
}
