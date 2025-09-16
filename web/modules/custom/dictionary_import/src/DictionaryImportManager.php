<?php

declare(strict_types=1);

namespace Drupal\dictionary_import;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drush\Exceptions\CommandFailedException;
use GuzzleHttp\ClientInterface;
use function var_dump;

/**
 * @todo Add class description.
 */
final class DictionaryImportManager implements DictionaryImportManagerInterface {

  const string API_URL = 'https://api.dictionaryapi.dev/api/v2/entries/en/';

  /**
   * Constructs a DictionaryImportManager object.
   */
  public function __construct(
    private readonly ClientInterface $httpClient,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly LoggerChannelInterface $logger,
  ) {}

  /**
   * Requests a word entry from Dictionary REST API.
   *
   * @param string $word
   *
   * @return array
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function requestApiWord(string $word): array {
    $word = trim($word);
    $url = self::API_URL . $word;

    $response = $this->httpClient->request('GET', $url);

    $body = (string) $response->getBody();
    return json_decode($body, TRUE);
  }

  /**
   * Builds node content array for entity type manager from API data.
   *
   * @param array $word
   *
   * @return array
   */
  private function buildNodeContentArray(array $word): array {
    $title = $word[0]['word'];

    $definitions = [];
    foreach ($word as $entry) {
      foreach ($entry['meanings'] as $meaning) {
        $definitions = array_merge($definitions, array_column($meaning['definitions'], 'definition'));
      }
    }

    return [
      'type' => 'dictionary_entry',
      'title' => $title,
      'field_word' => $title,
      'field_definitions' => $definitions,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function createOrUpdateEntry(array $word): int {
    $content = $this->buildNodeContentArray($word);

    $existing_nodes = $this->entityTypeManager->getStorage('node')
                                              ->loadByProperties(['field_word' => $content['field_word']]);

    if (empty($existing_nodes)) {
      $entity = $this->entityTypeManager->getStorage('node')
                                        ->create($content);
    }
    else {
      $entity = reset($existing_nodes);
      $entity = $this->updateNodeEntityValues($entity, $content);
    }

    $violations = $entity->validate();
    if ($violations->count() > 0) {
      throw new CommandFailedException('Node validation failed');
    }

    return $entity->save();
  }

  private function updateNodeEntityValues(EntityInterface $entity, array $content): EntityInterface {
    $entity->title = $content['title'];
    $entity->set('field_word', $content['field_word']);
    $entity->set('field_definitions', $content['field_definitions']);

    return $entity;
  }

}
