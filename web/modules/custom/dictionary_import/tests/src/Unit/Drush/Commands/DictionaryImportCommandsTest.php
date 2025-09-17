<?php

declare(strict_types=1);

namespace Drupal\Tests\dictionary_import\Unit\Drush\Commands;

use Drupal\dictionary_import\DictionaryImportManagerInterface;
use Drupal\dictionary_import\Drush\Commands\DictionaryImportCommands;
use Drupal\Tests\UnitTestCase;
use Drush\Commands\DrushCommands;
use Drush\Log\DrushLoggerManager;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Unit tests for DictionaryImportCommands class.
 */
class DictionaryImportCommandsTest extends UnitTestCase {

  private DictionaryImportCommands $command;

  private MockObject|DictionaryImportManagerInterface $mockDictionaryManager;

  private MockObject|DrushLoggerManager $mockDrushLogger;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // This constant is normally defined in core/includes/common.inc.
    if (!defined('SAVED_NEW')) {
      define('SAVED_NEW', 1);
    }

    $this->mockDictionaryManager = $this->createMock(DictionaryImportManagerInterface::class);
    $this->mockDrushLogger = $this->createMock(DrushLoggerManager::class);

    // Create the command instance with mocked dependencies.
    $this->command = new DictionaryImportCommands($this->mockDictionaryManager);

    $reflection = new \ReflectionClass($this->command);
    $loggerProperty = $reflection->getProperty('logger');
    $loggerProperty->setValue($this->command, $this->mockDrushLogger);
  }

  /**
   * Generates API response test data.
   *
   * @return array
   *   Mock API response structure.
   */
  private function getValidApiResponse(): array {
    return [
      [
        'word' => 'test',
        'meanings' => [
          [
            'definitions' => [
              [
                'definition' => 'A procedure intended to establish the quality, performance, or reliability of something.',
              ],
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * Tests successful word import scenario with new entry creation.
   */
  public function testImportWordSuccess(): void {
    $word = 'test';
    $apiResponse = $this->getValidApiResponse();

    // Mock the dictionary manager to return valid API response.
    $this->mockDictionaryManager
      ->expects($this->once())
      ->method('requestApiWord')
      ->with($word)
      ->willReturn($apiResponse);

    // Mock the dictionary manager to return SAVED_NEW for new entry creation.
    $this->mockDictionaryManager
      ->expects($this->once())
      ->method('createOrUpdateEntry')
      ->with($apiResponse)
      ->willReturn(SAVED_NEW);

    // Mock the logger to expect a success message for new entry creation.
    $this->mockDrushLogger
      ->expects($this->once())
      ->method('success')
      ->with('Dictionary Entry created successfully: ' . $word);

    $result = $this->command->importWord($word);

    $this->assertEquals(DrushCommands::EXIT_SUCCESS, $result);
  }

  /**
   * Tests API 404 error scenario when word is not found.
   */
  public function testImportWordApi404Error(): void {
    $word = 'nonexistentword';

    $mockRequest = $this->createMock(RequestInterface::class);

    $mockResponse = $this->createMock(ResponseInterface::class);
    $mockResponse->method('getStatusCode')->willReturn(404);

    // Create a RequestException with 404 status code.
    $requestException = new RequestException(
      'Not Found',
      $mockRequest,
      $mockResponse
    );

    // Mock the dictionary manager to throw RequestException with 404 status.
    $this->mockDictionaryManager
      ->expects($this->once())
      ->method('requestApiWord')
      ->with($word)
      ->willThrowException($requestException);

    // Mock the logger to expect an error message for 404 not found.
    $this->mockDrushLogger
      ->expects($this->once())
      ->method('error')
      ->with('API returned 404 not found for word: ' . $word);

    // Ensure createOrUpdateEntry is never called when API fails.
    $this->mockDictionaryManager
      ->expects($this->never())
      ->method('createOrUpdateEntry');

    $result = $this->command->importWord($word);

    $this->assertEquals(DrushCommands::EXIT_FAILURE, $result);
  }

}
