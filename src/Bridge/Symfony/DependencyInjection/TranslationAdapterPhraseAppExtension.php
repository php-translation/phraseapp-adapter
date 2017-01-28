<?php

namespace Translation\PlatformAdapter\PhraseApp\Bridge\Symfony\DependencyInjection;

use FAPI\PhraseApp\PhraseAppClient;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Translation\PlatformAdapter\PhraseApp\PhraseApp;

/**
 * @author Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 */
class TranslationAdapterPhraseAppExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration($container);
        $config = $this->processConfiguration($configuration, $configs);

        $apiDef = $container->register('php_translation.adapter.phrase_app.raw');
        $apiDef->setClass(PhraseAppClient::class);
        $apiDef->addArgument($config['token']);

        $adapterDef = $container->register('php_translation.adapter.phrase_app');
        $adapterDef
            ->setClass(PhraseApp::class)
            ->addArgument($apiDef)
            ->addArgument($config['project_id'])
            ->addArgument($config['locale_to_id_mapping']);
    }
}
