<?php

namespace Translation\PlatformAdapter\PhraseApp;

use FAPI\PhraseApp\Exception\Domain\UnprocessableEntityException;
use FAPI\PhraseApp\Model\Key\KeyCreated;
use FAPI\PhraseApp\Model\Key\KeySearchResults;
use FAPI\PhraseApp\Model\Translation\Index;
use FAPI\PhraseApp\PhraseAppClient;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Translation\Common\Exception\StorageException;
use Translation\Common\Model\Message;
use Translation\Common\Model\MessageInterface;
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

    /**
     * @var string|null
     */
    private $defaultLocale;

    public function __construct(
        PhraseAppClient $client,
        string $projectId,
        array $localeToIdMapping,
        array $domains,
        string $defaultLocale = null
    ) {
        $this->client = $client;
        $this->projectId = $projectId;
        $this->localeToIdMapping = $localeToIdMapping;
        $this->domains = $domains;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $locale, string $domain, string $key): ?MessageInterface
    {
        /* @var Index $index */
        $index = $this->client->translation()->indexLocale($this->projectId, $this->getLocaleId($locale), [
            'tags' => $domain
        ]);

        foreach ($index as $translation) {
            if ($translation->getKey()->getName() === $domain.'::'.$key) {
                return new Message($key, $domain, $locale, $translation->getContent(), []);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(MessageInterface $message): void
    {
        $localeId = $this->getLocaleId($message->getLocale());

        /* @var KeySearchResults $result */
        $result = $this->client->key()->search($this->projectId, [
            'tags' => $message->getDomain(),
            'name' => $message->getDomain().'::'.$message->getKey(),
        ]);

        foreach ($result as $key) {
            if ($key->getName() === $message->getDomain().'::'.$message->getKey()) {
                /* @var Index $index */
                $index = $this->client->translation()->indexKey($this->projectId, $key->getId(), ['tags' => $message->getDomain()]);
                foreach ($index as $translation) {
                    if ($translation->getLocale()->getId() === $localeId) {
                        // Translation does already exist
                        return;
                    }
                }

                // Create a translation with an existing key
                $this->client->translation()->create($this->projectId, $localeId, $key->getId(), $message->getTranslation());

                return;
            }
        }

        try {
            /* @var KeyCreated $keyCreated */
            $keyCreated = $this->client->key()->create($this->projectId, $message->getDomain().'::'.$message->getKey(), [
                'tags' => $message->getDomain(),
            ]);
        } catch (UnprocessableEntityException $e) {
            // Translaton does already exist
            return;
        }

        $this->client->translation()->create(
            $this->projectId,
            $this->getLocaleId($message->getLocale()),
            $keyCreated->getId(),
            $message->getTranslation()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function update(MessageInterface $message): void
    {
        $localeId = $this->getLocaleId($message->getLocale());
        /* @var KeySearchResults $results */
        $results = $this->client->key()->search($this->projectId, [
            'tags' => $message->getDomain(),
            'name' => $message->getDomain().'::'.$message->getKey()
        ]);

        foreach ($results as $searchResult) {
            if ($searchResult->getName() === $message->getDomain().'::'.$message->getKey()) {

                /* @var Index $translations */
                $translations = $this->client->translation()->indexKey($this->projectId, $searchResult->getId(), [
                    'tags' => $message->getDomain(),
                ]);

                foreach ($translations as $translation) {
                    if ($translation->getLocale()->getId() === $localeId) {
                        $this->client->translation()->update(
                            $this->projectId,
                            $translation->getId(),
                            $message->getTranslation()
                        );

                        return;
                    }
                }
            }
        }

        // No translation was found, lets create one.
        $this->create($message);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $locale, string $domain, string $key): void
    {
        /* @var KeySearchResults $results */
        $results = $this->client->key()->search($this->projectId, [
            'tags' => $domain,
            'name' => $domain.'::'.$key,
            'ids' => $this->getLocaleId($locale),
        ]);

        foreach ($results as $searchResult) {
            if ($searchResult->getName() === $domain.'::'.$key) {
                $this->client->key()->delete($this->projectId, $searchResult->getId());
                break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function export(MessageCatalogueInterface $catalogue, array $options = []): void
    {
        $locale = $catalogue->getLocale();
        $localeId = $this->getLocaleId($locale);

        foreach ($this->domains as $domain) {
            try {
                $response = $this->client->locale()->download($this->projectId, $localeId, 'symfony_xliff', [
                    'tag' => $domain
                ]);
            } catch (\Throwable $e) {
                throw new StorageException($e->getMessage());
            }

            try {
                $newCatalogue = XliffConverter::contentToCatalogue($response, $locale, $domain);

                $messages = [];

                foreach ($newCatalogue->all($domain) as $message => $translation) {
                    $messages[substr($message, strlen($domain) + 2)] = $translation;
                }

                $newCatalogue->replace($messages, $domain);

                $catalogue->addCatalogue($newCatalogue);
            } catch (\Throwable $e) {
                // ignore empty translation files
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function import(MessageCatalogueInterface $catalogue, array $options = []): void
    {
        $locale = $catalogue->getLocale();
        $localeId = $this->getLocaleId($locale);

        foreach ($this->domains as $domain) {
            $messages = [];

            foreach ($catalogue->all($domain) as $message => $translation) {
                $messages[$domain . '::' . $message] = $translation;
            }

            $catalogue->replace($messages, $domain);

            if ($this->defaultLocale) {
                $options = ['default_locale' => $this->defaultLocale];
            } else {
                $options = [];
            }

            $data = XliffConverter::catalogueToContent($catalogue, $domain, $options);

            $file = sys_get_temp_dir() . '/' . $domain . '.' . $locale . '.xlf';

            try {
                file_put_contents($file, $data);

                $this->client->upload()->upload($this->projectId, 'symfony_xliff', $file, [
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

    /**
     * @param string $locale
     *
     * @return string
     *
     * @throws StorageException If no id was found for locale.
     */
    private function getLocaleId(string $locale): string
    {
        if (isset($this->localeToIdMapping[$locale])) {
            return $this->localeToIdMapping[$locale];
        }

        throw new StorageException(sprintf('Id for locale "%s" has not been configured.', $locale));
    }
}
