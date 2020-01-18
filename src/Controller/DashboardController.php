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
   * @var \Drupal\fixerio\Storage
   */
  protected $storage;

  /**
   * Renderer service.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $formatter;
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->configFactory = $container->get('config.factory');
    $instance->formBuilder = $container->get('form_builder');
    $instance->currencies = $container->get('plugin.manager.currencies_plugin');
    $instance->storage = $container->get('fixerio.storage');
    $instance->renderer = $container->get('renderer');
    $instance->formatter = $container->get('date.formatter');
    return $instance;
  }

  /**
   * Rates.
   *
   * @return array
   *   Return Hello string.
   */
  public function rates() {
    try {
      $ratesData = $this->storage->load();
    }
    catch (\Exception $exception) {
      $this->messenger()->addError($exception->getMessage());
      return [
        '#markup' => 'Error',
      ];
    }
    $rows = [];
    foreach ($ratesData as $rate) {
      $rows[] = [
        'base' => mb_strtoupper($rate->base),
        'code' => mb_strtoupper($rate->code),
        'rate' => $rate->rate,
        'created' => $this->formatter->format($rate->created),
      ];
    }
    $header = [];
    $header['base'] = $this->t('Base');
    $header['code'] = $this->t('Currency code');
    $header['rate'] = $this->t('Rate');
    $header['created'] = $this->t('Updated');
    $build['content']['rates'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('Currency rates not available'),
    ];
    $build['#cache']['tags'][] = 'fixerio_rates';
    $build['#cache']['max-age'] = 3600;
    return $build;
  }

}
