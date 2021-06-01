<?php

namespace Translation\PlatformAdapter\PhraseApp\Bridge\Symfony\DependencyInjection;

use FAPI\PhraseApp\HttpClientConfigurator;
use FAPI\PhraseApp\PhraseAppClient;
use FAPI\PhraseApp\RequestBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
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
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $requestBuilder = (new Definition(RequestBuilder::class))
            ->addArgument(empty($config['httplug_message_factory']) ? null : new Reference($config['httplug_message_factory']));

        $clientConfigurator = (new Definition(HttpClientConfigurator::class))
            ->addArgument($config['token'])
            ->addArgument(empty($config['httplug_client']) ? null : new Reference($config['httplug_client']))
            ->addArgument(empty($config['httplug_uri_factory']) ? null : new Reference($config['httplug_uri_factory']));

        $apiDef = $container->register('php_translation.adapter.phrase_app.raw');
        $apiDef->setClass(PhraseAppClient::class)
            ->setFactory([PhraseAppClient::class, 'configure'])
            ->setPublic(true)
            ->addArgument($clientConfigurator)
            ->addArgument(null)
            ->addArgument($requestBuilder);

        $adapterDef = $container->register('php_translation.adapter.phrase_app');
        $adapterDef
            ->setClass(PhraseApp::class)
            ->setPublic(true)
            ->addArgument($apiDef)
            ->addArgument($config['project_id'])
            ->addArgument($config['locale_to_id_mapping'])
            ->addArgument($config['domains']);

        if (isset($config['default_locale'])) {
            $adapterDef->addArgument($config['default_locale']);
        }
    }
}
