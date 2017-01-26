<?php

namespace Translation\PlatformAdapter\PhraseApp;

use nediam\PhraseApp\PhraseAppClient;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Translation\Common\Exception\StorageException;
use Translation\Common\Model\Message;
use Translation\Common\Storage;
use Translation\Common\TransferableStorage;
use Translation\PlatformAdapter\PhraseApp\Bridge\Symfony\XliffConverter;

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

    public function __construct(PhraseAppClient $client, string $projectId, array $localeToIdMapping)
    {
        $this->client = $client;
        $this->projectId = $projectId;
        $this->localeToIdMapping = $localeToIdMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function get($locale, $domain, $key)
    {
        $translations = $this->client->request('translation.indexLocale', [
            'project_id' => $this->projectId,
            'locale_id' => $this->getLocaleId($locale),
        ]);

        foreach ($translations as $translation) {
            if ($translation['key']['name'] === "$domain::$key") {
                return new Message($key, $domain, $locale, substr($translation['content'], strlen($domain) + 2));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(Message $message)
    {
        try {
            $response = $this->client->request('key.search', [
                'project_id' => $this->projectId,
                'locale_id' => $this->getLocaleId($message->getLocale()),
                'q' => 'tags:' . $message->getDomain() . ' name:' . $message->getDomain() . '::' . $message->getKey(),
            ]);

            foreach ($response as $key) {
                if ($key['name'] === $message->getDomain() . '::' . $message->getKey()) {
                    $keyId = $key['id'];
                    break;
                }
            }

            if (!isset($keyId)) {
                $response = $this->client->request('key.create', [
                    'project_id' => $this->projectId,
                    'locale_id' => $this->getLocaleId($message->getLocale()),
                    'name' => $message->getDomain() . '::' . $message->getKey(),
                    'tags' => $message->getDomain(),
                ]);

                $keyId = $response['id'];
            }

            $response = $this->client->request('translation.indexKeys', [
                'project_id' => $this->projectId,
                'key_id' => $keyId
            ]);

            if (empty($response)) {
                $this->client->request('translation.create', [
                    'project_id' => $this->projectId,
                    'locale_id' => $this->getLocaleId($message->getLocale()),
                    'key_id' => $keyId,
                    'content' => $message->getDomain() . '::' . $message->getTranslation(),
                ]);

                return;
            }
        } catch (\Throwable $e) {
            throw new StorageException($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update(Message $message)
    {
        try {
            $response = $this->client->request('key.search', [
                'project_id' => $this->projectId,
                'locale_id' => $this->getLocaleId($message->getLocale()),
                'q' => 'tags:' . $message->getDomain() . ' name:' . $message->getDomain() . '::' . $message->getKey(),
            ]);

            foreach ($response as $key) {
                if ($key['name'] === $message->getDomain() . '::' . $message->getKey()) {
                    $keyId = $key['id'];
                    break;
                }
            }

            if (!isset($keyId)) {
                $response = $this->client->request('key.create', [
                    'project_id' => $this->projectId,
                    'locale_id' => $this->getLocaleId($message->getLocale()),
                    'name' => $message->getDomain() . '::' . $message->getKey(),
                    'tags' => $message->getDomain(),
                ]);

                $keyId = $response['id'];
            }

            $response = $this->client->request('translation.indexKeys', [
                'project_id' => $this->projectId,
                'key_id' => $keyId
            ]);

            if (empty($response)) {
                $this->client->request('translation.create', [
                    'project_id' => $this->projectId,
                    'locale_id' => $this->getLocaleId($message->getLocale()),
                    'key_id' => $keyId,
                    'content' => $message->getDomain() . '::' . $message->getTranslation(),
                ]);

                return;
            }

            foreach ($response as $translation) {
                if ($translation['locale']['name'] === $message->getLocale()) {
                    $id = $translation['id'];
                }
                break;
            }

            if (!isset($id)) {
                throw new StorageException('Translation id not found.');
            }

            $this->client->request('translation.update', [
                'project_id' => $this->projectId,
                'id' => $id,
                'content' => $message->getDomain() . '::' . $message->getTranslation(),
            ]);
        } catch (\Throwable $e) {
            throw new StorageException($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($locale, $domain, $key)
    {
        try {
            $response = $this->client->request('key.search', [
                'project_id' => $this->projectId,
                'locale_id' => $this->getLocaleId($locale),
                'q' => $domain . '::' . $key,
            ]);

            foreach ($response as $keyName) {
                if ($keyName['name'] === $key) {
                    $keyId = $key['id'];
                    break;
                }
            }

            if (!isset($keyId)) {
                return;
            }

            $this->client->request('key.destroy', [
                'project_id' => $this->projectId,
                'id' => $keyId

            ]);
        } catch (\Throwable $e) {
            throw new StorageException($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function export(MessageCatalogueInterface $catalogue)
    {
        $locale = $catalogue->getLocale();

        foreach ($catalogue->getDomains() as $domain) {
            try {
                $response = $this->client->request('locale.download', [
                    'project_id' => $this->projectId,
                    'id' => $this->getLocaleId($locale),
                    'tag' => $domain,
                    'file_format' => 'symfony_xliff'
                ]);
            } catch (\Throwable $e) {
                throw new StorageException($e->getMessage());
            }

            /* @var \GuzzleHttp\Stream\Stream $data */
            $data = $response['text'];
            $catalogue->addCatalogue(XliffConverter::contentToCatalogue($data->getContents(), $locale, $domain));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function import(MessageCatalogueInterface $catalogue)
    {
        foreach ($this->localeToIdMapping as $locale => $localeId) {
            foreach ($catalogue->getDomains() as $domain) {
                $data = XliffConverter::catalogueToContent($catalogue, $domain);
                $file = sys_get_temp_dir() . '/' . $domain . '_' . $locale . '.xlf';
                file_put_contents($file, $data);

                try {
                    /* I could not get guzzle to work with this, so fallback to curl for now
                     *
                    $response = $this->client->request('upload.create', [
                        'project_id' => $this->projectId,
                        'locale_id' => $localeId,
                        'file' => '@'.$file,
                        'file_format' => 'symfony_xliff',
                        'tags' => $domain
                    ]);
                    */

                    $ch = curl_init();

                    curl_setopt($ch, \CURLOPT_URL, 'https://api.phraseapp.com/api/v2/projects/' . $this->projectId . '/uploads');
                    curl_setopt($ch, \CURLOPT_USERPWD, $this->client->getToken());
                    curl_setopt($ch, \CURLOPT_HEADER, 0);
                    curl_setopt($ch, \CURLOPT_POST, 1);
                    curl_setopt($ch, \CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_VERBOSE, 1);
                    curl_setopt($ch, \CURLOPT_POSTFIELDS, [
                        'file' => new \CURLFile($file),
                        'file_format' => 'symfony_xliff',
                        'tags' => $domain,
                        'locale_id' => $localeId
                    ]);

                    curl_exec($ch);
                    curl_close($ch);
                } catch (\Throwable $e) {
                    throw new StorageException($e->getMessage());
                } finally {
                    unlink($file);
                }
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
