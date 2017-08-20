<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\PlatformAdapter\PhraseApp\Tests\Functional;

use Http\HttplugBundle\HttplugBundle;
use Nyholm\BundleTest\BaseBundleTestCase;
use Translation\PlatformAdapter\PhraseApp\Bridge\Symfony\TranslationAdapterPhraseAppBundle;
use Translation\PlatformAdapter\PhraseApp\PhraseApp;

class BundleInitializationTest extends BaseBundleTestCase
{
    protected function getBundleClass()
    {
        return TranslationAdapterPhraseAppBundle::class;
    }

    public function testRegisterBundle()
    {
        $kernel = $this->createKernel();
        $kernel->addBundle(HttplugBundle::class);

        // Add some configuration
        $kernel->addConfigFile(__DIR__.'/config/default.yml');

        $this->bootKernel();
        $container = $this->getContainer();

        $this->assertTrue($container->has('php_translation.adapter.phrase_app'));
        $service = $container->get('php_translation.adapter.phrase_app');
        $this->assertInstanceOf(PhraseApp::class, $service);
    }
}
