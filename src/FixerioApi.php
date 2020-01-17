<?php

namespace Drupal\fixerio;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class FixerioApi.
 */
class FixerioApi implements FixerioApiInterface {

  const API = 'http://data.fixer.io/api/';

  const SYMBOLS_ENDPOINT = 'symbols';

  /**
   * Depending on your subscription plan, the API's latest endpoint will return
   *  real-time exchange rate data updated every 60 minutes, every 10 minutes
   *  or every 60 seconds.
   */
  const LATEST_ENDPOINT = 'latest';
  /**
   * GuzzleHttp\ClientInterface definition.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Drupal\Core\Logger\LoggerChannelInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Symfony\Component\Serializer\SerializerInterface definition.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  protected $serializer;

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Drupal\Core\Config\ImmutableConfig definition.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * API key.
   *
   * @var string
   */
  protected $apiKey;

  /**
   * Constructs a new FixerioApi object.
   */
  public function __construct(ClientInterface $http_client, LoggerChannelInterface $logger, SerializerInterface $serializer, ConfigFactoryInterface $configFactory) {
    $this->httpClient = $http_client;
    $this->logger = $logger;
    $this->serializer = $serializer;
    $this->configFactory = $configFactory;
    $this->config = $configFactory->get('fixerio.settings');
  }

  /**
   * Return API key.
   *
   * @return string
   *   API key.
   */
  private function apiKey():string {
    if (empty($this->apiKey)) {
      $this->apiKey = $this->config->getOriginal('api_access_key');
    }
    return $this->apiKey;
  }

  /**
   * @param $endpoint
   * @param array $params
   * @todo Use Response class
   * @return mixed
   */
  private function request($endpoint, array $params = []) {
    $params['access_key'] = $this->apiKey();
    $endpoint = self::API . $endpoint;

    try {
      $response = $this->httpClient->request('GET', $endpoint, [
        'query' => $params,
      ]);
      $contents = $response->getBody()->getContents();
      return Json::decode($contents);
    }
    catch (GuzzleException $e) {
      $this->logger->error($e->getMessage());
      return [];
    }
  }

  /**
   * @todo Use error handler
   * @param array $error
   */
  public function errorHandler(array $error) {

  }

  /**
   * @todo: Store in cache key: fixerio_symbols.
   *
   * @return array|mixed
   */
  public function symbols() {

    $response_data = $this->request(self::SYMBOLS_ENDPOINT);

    if (!$response_data['success']) {
      $this->errorHandler($response_data['error']);
      return [];
    }
    else {
      return $response_data['symbols'];
    }
  }

  /**
   * Update rates.
   */
  public function latest() {
    $currencies = $this->config->get('available_currencies');

    $params['symbols'] = implode(',', $currencies);
    if ($this->config->get('plan') !== 'free') {
      $params['base'] = $this->config->get('base');
    }

    $response_data = $this->request(self::LATEST_ENDPOINT, $params);

    if (!$response_data['success'] && isset($response_data['error'])) {
      $this->errorHandler($response_data['error']);
    }
    $rates = [];
    foreach ($response_data['rates'] as $code => $value) {
      $rates[mb_strtolower(($code))] = [
        'code' => $code,
        'rate' => $value,
      ];
    }
    $base = mb_strtolower($response_data['base']);
    $ratesStorage = $this->configFactory->getEditable('fixerio.rates.' . $base);

    $ratesStorage->set('base', $base)
      ->set('date', $response_data['date'])
      ->set('last_update', $response_data['timestamp'])
      ->set('rates', $rates)->save();

    $this->logger->notice(t('Update latest rates for @base', [
      '@base' => $response_data['base'],
    ]));
  }

}
