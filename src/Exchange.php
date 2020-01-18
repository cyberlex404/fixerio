<?php

namespace Drupal\fixerio;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\fixerio\Exception\UnavailableCurrency;
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
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Fixer config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * @var string
   */
  protected $base;

  /**
   * @var array
   */
  protected $rates;

  /**
   * @var array
   */
  protected $available_currencies;

  protected $storage;

  /**
   * Constructs a new Exchange object.
   *
   * @param \Drupal\fixerio\FixerioApiInterface $api
   *   API.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Logger.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_default
   *   Cache.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   * @param \Drupal\fixerio\Storage $storage
   */
  public function __construct(FixerioApiInterface $api, LoggerChannelInterface $logger, CacheBackendInterface $cache_default, ConfigFactoryInterface $configFactory, Storage $storage) {
    $this->api = $api;
    $this->logger = $logger;
    $this->cacheDefault = $cache_default;
    $this->configFactory = $configFactory;
    $this->storage = $storage;
  }

  /**
   * {@inheritdoc}
   */
  public function convert(float $value, string $source, string $target):float {
    $source = mb_strtolower($source);
    $target = mb_strtolower($target);
    $available = array_unique(array_merge($this->getAvailableCurrencies(), [$this->base()]));
    if (!in_array($source, $available)) {
      throw new UnavailableCurrency(mb_strtoupper($source) . ' currency not available for conversion');
    }
    if (!in_array($target, $available)) {
      throw new UnavailableCurrency(mb_strtoupper($target) . ' currency not available for conversion');
    }

    if ($source == $this->base()) {
      return $value * $this->exchangeRate($target);
    }
    elseif ($target == $this->base()) {
      return $value / $this->exchangeRate($source);
    }
    else {
      $toBaseRate = $this->exchangeRate($source);
      $toTargetRate = $this->exchangeRate($target);
      return $value / $toBaseRate * $toTargetRate;
    }
  }

  /**
   * Return rate by currency.
   *
   * @param string $code
   *   Currency code.
   *
   * @return float
   *   Rate.
   */
  private function exchangeRate(string $code):float {
    $rates = $this->rates();
    return $rates[$code];
  }

  /**
   * Helper function.
   *
   * @return array
   *   rates array
   */
  private function rates($codes = []) {
    if (empty($this->rates)) {
      $base = $this->base();
      $ratesData = $this->storage->load(['base' => $base]);
      $rates = [];
      foreach ($ratesData as $key => $rate) {
        $rates[$rate->code] = $rate->rate();
      }
      $this->rates = $rates;
    }
    return $this->rates;
  }

  /**
   * Helper function.
   */
  private function getAvailableCurrencies() {
    if (empty($this->available_currencies)) {
      $this->available_currencies = array_keys($this->rates());
    }
    return $this->available_currencies;
  }

  /**
   * @return string|null
   */
  private function base() {
    if (empty($this->base)) {
      $this->base = mb_strtolower($this->config()->get('base'));
    }
    return $this->base;
  }

  /**
   * Helper function.
   */
  private function config() {
    if (empty($this->config)) {
      $this->config = $this->configFactory->get('fixerio.settings');
    }
    return $this->config;
  }

}
