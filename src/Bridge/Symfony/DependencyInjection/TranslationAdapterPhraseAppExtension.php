<?php

namespace Translation\PlatformAdapter\PhraseApp\Bridge\Symfony\DependencyInjection;

use nediam\PhraseApp\PhraseAppClient;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Translation\PlatformAdapter\PhraseApp\Bridge\Symfony\XliffFileDumper;
use Translation\PlatformAdapter\PhraseApp\PhraseApp;

class TranslationAdapterPhraseAppExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration($container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('translation.dumper.xliff.class', XliffFileDumper::class);

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
