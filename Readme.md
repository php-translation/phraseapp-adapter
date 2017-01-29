# Adapter for PhraseApp

[![Latest Version](https://img.shields.io/github/release/php-translation/phraseapp-adapter.svg?style=flat-square)](https://github.com/php-translation/phraseapp-adapter/releases)
[![Build Status](https://img.shields.io/travis/php-translation/phraseapp-adapter.svg?style=flat-square)](https://travis-ci.org/php-translation/phraseapp-adapter)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/php-translation/phraseapp-adapter.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-translation/phraseapp-adapter)
[![Quality Score](https://img.shields.io/scrutinizer/g/php-translation/phraseapp-adapter.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-translation/phraseapp-adapter)
[![Total Downloads](https://img.shields.io/packagist/dt/php-translation/phraseapp-adapter.svg?style=flat-square)](https://packagist.org/packages/php-translation/phraseapp-adapter)

This is an PHP-translation adapter for PhraseApp ([phraseapp.com](https://phraseapp.com/)). 

## Install

```bash
composer require php-translation/phraseapp-adapter
```

## Symfony bundle

If you want to use the Symfony bundle you may activate it in kernel:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Translation\PlatformAdapter\Phraseapp\Bridge\Symfony\TranslationAdapterPhraseAppBundle(),
    );
}
```

### Example configuration

``` yaml
# /app/config/config.yml
translation_adapter_phrase_app:
    httplug_client: httplug.client.default
    httplug_message_factory: httplug.message_factory.default
    httplug_uri_factory: httplug.uri_factory.default
    project_id: <your project id>
    locale_to_id_mapping:
        de: <de locale id>
        en: <en locale id>
        fr: <fr locale id>
    token: <your phrase app token>
    default_locale: en
    domains: ["<your>", "<list>", "<of>", "<domains>"]
```

This will produce a service named `php_translation.adapter.phrase_app` that could be used in the configuration for
the [Translation Bundle](https://github.com/php-translation/symfony-bundle).

## Documentation

Read our documentation at [http://php-translation.readthedocs.io](http://php-translation.readthedocs.io/en/latest/).

## Contribute

Do you want to make a change? Pull requests are welcome.
