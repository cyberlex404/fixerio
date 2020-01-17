<?php

namespace Drupal\fixerio;

use Drupal\fixerio\FixerioApiInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Class Exchange.
 */
class Exchange implements ExchangeInterface {

  /**
   * Drupal\fixerio\FixerioApiInterface definition.
   *
   * @var \Drupal\fixerio\FixerioApiInterface
   */
  protected $api;

  /**
   * Drupal\Core\Logger\LoggerChannelInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Drupal\Core\Cache\CacheBackendInterface definition.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheDefault;

  /**
   * Constructs a new Exchange object.
   *
   * @param \Drupal\fixerio\FixerioApiInterface $api
   *   API.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Logger.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_default
   *   Cache.
   */
  public function __construct(FixerioApiInterface $api, LoggerChannelInterface $logger, CacheBackendInterface $cache_default) {
    $this->api = $api;
    $this->logger = $logger;
    $this->cacheDefault = $cache_default;
  }

  /**
   * {@inheritdoc}
   */
  public function convert(float $value, string $source, string $target): float {
    // TODO: Implement convert() method.
  }

}
