<?php

namespace Drupal\fixerio\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DashboardController.
 */
class DashboardController extends ControllerBase {

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Drupal\Core\Form\FormBuilderInterface definition.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Drupal\fixerio\CurrenciesPluginManagerInterface definition.
   *
   * @var \Drupal\fixerio\CurrenciesPluginManagerInterface
   */
  protected $currencies;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->configFactory = $container->get('config.factory');
    $instance->formBuilder = $container->get('form_builder');
    $instance->currencies = $container->get('plugin.manager.currencies_plugin');
    return $instance;
  }

  /**
   * Rates.
   *
   * @return array
   *   Return Hello string.
   */
  public function rates() {

    $base = $this->configFactory->get('fixerio.settings')->get('base');
    $ratesData = $this->configFactory->get('fixerio.rates.' . $base);

    $build['content']['info'] = [
      '#markup' => $ratesData->get('date'),
    ];
    $rows = [];
    foreach ($ratesData->get('rates') as $code => $rate) {
      $rows[] = [
        'code' => $rate['code'],
        'rate' => $rate['rate'],
      ];
    }

    $header = [];
    $header['code'] = $this->t('Code');
    $header['rate'] = $this->t('Rate');
    $build['content']['rates'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('Currency rates not available'),
      '#caption' => $this->t('Exchange rate table: @base. Update: @update', [
        '@base' => $base,
        '@update' => $ratesData->get('date'),
      ]),
    ];

    /** @var \Drupal\Core\Render\Renderer $renderer */
    $renderer = \Drupal::service('renderer');
    $renderer->addCacheableDependency($build, $ratesData);
    return $build;
  }

}
