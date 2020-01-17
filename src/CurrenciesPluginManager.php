<?php

namespace Drupal\fixerio;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides the default currencies_plugin manager.
 */
class CurrenciesPluginManager extends DefaultPluginManager implements CurrenciesPluginManagerInterface {

  use StringTranslationTrait;
  /**
   * Provides default values for all currencies_plugin plugins.
   *
   * @var array
   */
  protected $defaults = [
    // Add required and optional plugin properties.
    'id' => '',
    'label' => '',
    'code' => '',
  ];

  /**
   * Constructs a new CurrenciesPluginManager object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   */
  public function __construct(ModuleHandlerInterface $module_handler, CacheBackendInterface $cache_backend) {
    // Add more services as required.
    $this->moduleHandler = $module_handler;
    $this->setCacheBackend($cache_backend, 'currencies_plugin', ['currencies_plugin']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!isset($this->discovery)) {
      $this->discovery = new YamlDiscovery('currencies.plugin', $this->moduleHandler->getModuleDirectories());
      $this->discovery->addTranslatableProperty('label', 'label_context');
      $this->discovery = new ContainerDerivativeDiscoveryDecorator($this->discovery);
    }
    return $this->discovery;
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);

    // You can add validation of the plugin definition here.
    if (empty($definition['id'])) {
      throw new PluginException(sprintf('Example plugin property (%s) definition "id" is required.', $plugin_id));
    }
    if (empty($definition['code'])) {
      throw new PluginException(sprintf('Plugin property (%s) definition "code" is required.', $plugin_id));
    }
  }

  // Add other methods here as defined in the CurrenciesPluginManagerInterface.

  /**
   * {@inheritdoc}
   */
  public function getCurrencies() {
    $list = $this->getDefinitions();
    return $list;
  }

  /**
   * {@inheritdoc}
   */
  public function currenciesOptionsList() {
    $options = [];
    foreach ($this->getDefinitions() as $definition) {
      $key = mb_strtolower($definition['code']);
      // $options[$key] = $definition['label'];
      $options[$key] = $this->t('@name (@code)', [
        '@name' => $definition['label'],
        '@code' => $definition['code'],
      ]);
    }
    return $options;
  }

}
