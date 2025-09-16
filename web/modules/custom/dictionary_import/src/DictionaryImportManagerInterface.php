<?php

declare(strict_types=1);

namespace Drupal\dictionary_import;

use Drupal\Core\Entity\EntityInterface;

/**
 * @todo Add interface description.
 */
interface DictionaryImportManagerInterface {

  public function requestApiWord(string $word): array;

  /**
   * @todo Add method description.
   */
  public function createOrUpdateEntry(array $word): int;
}
