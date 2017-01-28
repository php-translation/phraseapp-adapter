<?php

namespace Translation\PlatformAdapter\PhraseApp\Bridge\Symfony\DependencyInjection;

use FAPI\PhraseApp\HttpClientConfigurator;
use FAPI\PhraseApp\PhraseAppClient;
use FAPI\PhraseApp\RequestBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Translation\PlatformAdapter\PhraseApp\Bridge\Symfony\XliffFileDumper;
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

        $container->setParameter('translation.dumper.xliff.class', XliffFileDumper::class);

        $requestBuilder = (new Definition(RequestBuilder::class))
            ->addArgument(new Reference($config['httplug_message_factory']));

        $clientConfigurator = (new Definition(HttpClientConfigurator::class))
            ->addArgument($config['token'])
            ->addArgument(new Reference($config['httplug_client']))
            ->addArgument(new Reference($config['httplug_uri_factory']));

        $apiDef = $container->register('php_translation.adapter.phrase_app.raw');
        $apiDef->setClass(PhraseAppClient::class)
            ->setFactory([PhraseAppClient::class, 'configure'])
            ->addArgument($clientConfigurator)
            ->addArgument(null)
            ->addArgument($requestBuilder);

        $adapterDef = $container->register('php_translation.adapter.phrase_app');
        $adapterDef
            ->setClass(PhraseApp::class)
            ->addArgument($apiDef)
            ->addArgument($config['project_id'])
            ->addArgument($config['locale_to_id_mapping'])
            ->addArgument($config['domains']);
    }
}
