# Adapter for Phraseapp

[![Latest Version](https://img.shields.io/github/release/php-translation/phraseapp-adapter.svg?style=flat-square)](https://github.com/php-translation/phraseapp-adapter/releases)
[![Build Status](https://img.shields.io/travis/php-translation/phraseapp-adapter.svg?style=flat-square)](https://travis-ci.org/php-translation/phraseapp-adapter)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/php-translation/phraseapp-adapter.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-translation/phraseapp-adapter)
[![Quality Score](https://img.shields.io/scrutinizer/g/php-translation/phraseapp-adapter.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-translation/phraseapp-adapter)
[![Total Downloads](https://img.shields.io/packagist/dt/php-translation/phraseapp-adapter.svg?style=flat-square)](https://packagist.org/packages/php-translation/phraseapp-adapter)

This is an PHP-translation adapter for Phraseapp ([phraseapp.com](https://phraseapp.com/)). 

### Install

```bash
composer require php-translation/phraseapp-adapter
```

##### Symfony bundle

If you want to use the Symfony bundle you may activate it in kernel:

```
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Translation\PlatformAdapter\Phraseapp\Bridge\Symfony\TranslationAdapterPhraseappBundle(),
    );
}
```

If you have one phraseapp project per domain you may configure the bundle like this: 
``` yaml
# /app/config/config.yml
translation_adapter_phraseapp:
  projects:
    messages:
      api_key: 'foobar'
    navigation:
      api_key: 'bazbar'
```

If you just doing one project and have tags for all your translation domains you may use this configuration:
``` yaml

# /app/config/config.yml
translation_adapter_phraseapp:
  projects:
    acme:
      api_key: 'foobar'
      domains: ['messages', 'navigation']
```

This will produce a service named `php_translation.adapter.phraseapp` that could be used in the configuration for
the [Translation Bundle](https://github.com/php-translation/symfony-bundle).

### Documentation

Read our documentation at [http://php-translation.readthedocs.io](http://php-translation.readthedocs.io/en/latest/).

