<?php

namespace Drupal\fixerio;

use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelInterface;

/**
 * Class Storage.
 *
 * @todo Add new loadByCodes() method.
 *
 * @package Drupal\fixerio
 */
class Storage {

  const TABLE_NAME = 'fixerio_exchange';

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Drupal\Core\Logger\LoggerChannelInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Construct a repository object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Logger.
   */
  public function __construct(Connection $connection, LoggerChannelInterface $logger) {
    $this->connection = $connection;
    $this->logger = $logger;
  }

  /**
   * Save an entry in the database.
   *
   * Exception handling is shown in this example. It could be simplified
   * without the try/catch blocks, but since an insert will throw an exception
   * and terminate your application if the exception is not handled, it is best
   * to employ try/catch.
   *
   * @param array $entry
   *   An array containing all the fields of the database record.
   *
   * @return int
   *   The number of updated rows.
   *
   * @throws \Exception
   *   When the database insert fails.
   */
  public function insert(array $entry) {
    try {
      $return_value = $this->connection->insert(self::TABLE_NAME)
        ->fields($entry)
        ->execute();
    }
    catch (\Exception $e) {
      $this->logger->alert(t('Insert failed. Message = %message', [
        '%message' => $e->getMessage(),
      ]));
    }
    return $return_value ?? NULL;
  }

  /**
   * Read from the database using a filter array.
   * @todo Filtred value
   *
   * @param array $entry
   *   An array containing all the fields used to search the entries in the
   *   table.
   *
   * @return object[]
   *   An object containing the loaded entries if found.
   *
   * @see \Drupal\Core\Database\Connection::select()
   */
  public function load(array $entry = []) {
    $select = $this->connection
      ->select(self::TABLE_NAME, 'r', ['fetch' => Rate::class])
      ->fields('r', ['base', 'code', 'rate', 'created']);

    foreach ($entry as $field => $value) {
      $select->condition($field, $value);
    }
    return $select->execute()->fetchAll();
  }

  /**
   * Update rates records.
   *
   * @param array $entry
   *   Rate data.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|null
   *   Record id
   */
  public function merge(array $entry) {
    $base = $entry['base'];
    $code = $entry['code'];
    try {
      $return_value = $this->connection->merge(self::TABLE_NAME)
        ->keys(['code', 'base'], [$code, $base])
        ->insertFields([
          'base' => $base,
          'code' => $code,
          'rate' => $entry['rate'],
          'created' => $entry['created'],
        ])
        ->updateFields([
          'rate' => $entry['rate'],
          'created' => $entry['created'],
        ])
        ->execute();
    }
    catch (\Exception $e) {
      $this->logger->error(t('Merge failed. Message = %message', [
        '%message' => $e->getMessage(),
      ]));
    }
    return $return_value ?? NULL;
  }

}
