<?php

namespace Translation\PlatformAdapter\PhraseApp\Bridge\Symfony;

use Symfony\Component\Translation\MessageCatalogue;
use Translation\SymfonyStorage\Dumper\XliffDumper;
use Translation\SymfonyStorage\Loader\XliffLoader;

/**
 * Utility class to convert between a MessageCatalogue and XLIFF file content.
 *
 * This class can be removed when https://github.com/php-translation/symfony-storage/pull/8 is accepted and merged
 */
final class XliffConverter
{
    /**
     * Create a catalogue from the contents of a XLIFF file.
     *
     * @param string $content
     * @param string $locale
     * @param string $domain
     *
     * @return MessageCatalogue
     */
    public static function contentToCatalogue($content, $locale, $domain)
    {
        $loader = new XliffLoader();
        $catalogue = new MessageCatalogue($locale);
        $loader->extractFromContent($content, $catalogue, $domain);

        return $catalogue;
    }

    /**
     * @param MessageCatalogue $catalogue
     * @param string           $domain
     * @param array            $options
     *
     * @return string
     */
    public static function catalogueToContent(MessageCatalogue $catalogue, $domain, array $options = [])
    {
        $dumper = new XliffDumper();

        return $dumper->getFormattedCatalogue($catalogue, $domain, $options);
    }
}
