<?php

declare(strict_types=1);

namespace Drupal\dictionary_import;

/**
 * Dictionary import manager service interface.
 */
interface DictionaryImportManagerInterface {

  /**
   * Requests a word entry from Dictionary REST API.
   *
   * @param string $word
   *   Word parameter for API request.
   *
   * @return array
   *   API response body.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function requestApiWord(string $word): array;

  /**
   * Create or update a Dictionary Entry node.
   *
   * @param array $word
   *   Word data returned by Dictionary REST API.
   *
   * @return int
   *   SAVED_NEW or SAVED_UPDATED.
   */
  public function createOrUpdateEntry(array $word): int;

}
