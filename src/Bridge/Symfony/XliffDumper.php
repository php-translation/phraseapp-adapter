<?php

namespace Translation\PlatformAdapter\PhraseApp\Bridge\Symfony;

use Symfony\Component\Translation\MessageCatalogue;

final class XliffDumper extends XliffFileDumper
{
    /**
     * Alias for formatCatalogue to provide a BC bridge.
     *
     * @param MessageCatalogue $messages
     * @param string           $domain
     * @param array            $options
     *
     * @return string
     */
    public function getFormattedCatalogue(MessageCatalogue $messages, $domain, array $options = [])
    {
        if (method_exists($this, 'formatCatalogue')) {
            return parent::formatCatalogue($messages, $domain, $options);
        }

        return $this->format($messages, $domain);
    }
}
