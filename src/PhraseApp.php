<?php

namespace Translation\PlatformAdapter\PhraseApp;

use FAPI\PhraseApp\PhraseAppClient;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Translation\Common\Exception\StorageException;
use Translation\Common\Model\Message;
use Translation\Common\Storage;
use Translation\Common\TransferableStorage;
use Translation\SymfonyStorage\XliffConverter;

/**
 * @author Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 */
class PhraseApp implements Storage, TransferableStorage
{
    /**
     * @var PhraseAppClient
     */
    private $client;

    /**
     * @var string
     */
    private $projectId;

    /**
     * @var array
     */
    private $localeToIdMapping;

    /**
     * @var array
     */
    private $domains;

    public function __construct(PhraseAppClient $client, string $projectId, array $localeToIdMapping, array $domains)
    {
        $this->client = $client;
        $this->projectId = $projectId;
        $this->localeToIdMapping = $localeToIdMapping;
        $this->domains = $domains;
    }

    public function get($locale, $domain, $key)
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function create(Message $message)
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function update(Message $message)
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function delete($locale, $domain, $key)
    {
        throw new \BadMethodCallException('Not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function export(MessageCatalogueInterface $catalogue)
    {
        $locale = $catalogue->getLocale();
        $localeId = $this->getLocaleId($locale);

        foreach ($this->domains as $domain) {
            try {
                $response = $this->client->export()->locale($this->projectId, $localeId, 'symfony_xliff', [
                    'tag' => $domain
                ]);
            } catch (\Throwable $e) {
                throw new StorageException($e->getMessage());
            }

            try {
                $catalogue->addCatalogue(XliffConverter::contentToCatalogue($response, $locale, $domain));
            } catch (\Throwable $e) {
                // ignore empty translation files
            }
        }

        return $catalogue;
    }

    /**
     * {@inheritdoc}
     */
    public function import(MessageCatalogueInterface $catalogue)
    {
        $locale = $catalogue->getLocale();
        $localeId = $this->getLocaleId($locale);

        foreach ($this->domains as $domain) {
            $data = XliffConverter::catalogueToContent($catalogue, $domain);

            $file = sys_get_temp_dir() . '/' . $domain . '.' . $locale . '.xlf';

            try {
                file_put_contents($file, $data);

                $this->client->import()->import($this->projectId, 'symfony_xliff', $file, [
                    'locale_id' => $localeId,
                    'tags' => $domain,
                ]);
            } catch (\Throwable $e) {
                throw new StorageException($e->getMessage());
            } finally {
                unlink($file);
            }
        }
    }

    private function getLocaleId(string $locale): string
    {
        if (isset($this->localeToIdMapping[$locale])) {
            return $this->localeToIdMapping[$locale];
        }

        throw new StorageException(sprintf('Id for locale "%s" has not been configured.', $locale));
    }
}
