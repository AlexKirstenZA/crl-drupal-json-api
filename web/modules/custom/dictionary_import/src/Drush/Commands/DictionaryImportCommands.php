<?php

namespace Drupal\dictionary_import\Drush\Commands;

use Drupal\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\dictionary_import\DictionaryImportManagerInterface;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\CommandFailedException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

/**
 * A Drush commandfile.
 */
final class DictionaryImportCommands extends DrushCommands {

  /**
   * Constructs a DictionaryImportCommands object.
   */
  public function __construct(
    private readonly DictionaryImportManagerInterface $dictionaryImportManager,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dictionary_import.manager'),
    );
  }

  /**
   * Import Dictionary Entry word via REST API.
   */
  #[CLI\Command(name: 'dictionary_entry:import', aliases: ['dei'])]
  #[CLI\Argument(name: 'word', description: 'Word to import via API.')]
  #[CLI\Usage(name: 'dictionary_entry:import foo', description: 'Import the word "foo" via REST API.')]
  public function importWord(string $word) {
    // Request word entry from dictionary REST API.
    try {
      $api_entry = $this->dictionaryImportManager->requestApiWord($word);
    }
    catch (RequestException $e) {
      $status_code = $e->getCode();
      if ($status_code === 404) {
        $this->logger->error('API returned 404 not found for word: ' . $word);
      }
      else {
        $this->logger->error('API returned a non-200 status code: ' . $status_code);
      }
      return DrushCommands::EXIT_FAILURE;
    }
    catch (GuzzleException $e) {
      $this->logger->error('An error occurred while performing API request: ' . $e->getMessage());
      return DrushCommands::EXIT_FAILURE;
    }

    // Create or update Dictionary Entry node.
    try {
      $node_status = $this->dictionaryImportManager->createOrUpdateEntry($api_entry);
    }
    catch (CommandFailedException $e) {
      $this->logger->error('An error occurred while processing node: ' . $e->getMessage());
      return DrushCommands::EXIT_FAILURE;
    }
    catch (EntityStorageException $e) {
      $this->logger->error('An error occurred while saving node: ' . $e->getMessage());
      return DrushCommands::EXIT_FAILURE;
    }

    if ($node_status === SAVED_NEW) {
      $this->logger()->success('Dictionary Entry created successfully: ' . $word);
    }
    else {
      $this->logger()->success('Dictionary Entry updated successfully: ' . $word);
    }

    return DrushCommands::EXIT_SUCCESS;
  }

}
