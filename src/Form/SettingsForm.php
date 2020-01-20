<?php

namespace Drupal\fixerio\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\fixerio\Exception\UnavailableCurrency;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SettingsForm.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * @var \Drupal\fixerio\FixerioApi
   */
  protected $api;
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->api = $container->get('fixerio.api');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'fixerio.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'fixerio/form';
    /** @var \Drupal\fixerio\ExchangeInterface $exchange */
    $exchange = \Drupal::service('fixerio.exchange');

    // @todo Remove dev tests
    try {
      $value = $exchange->convert(10, 'RON', 'EUR');
      \Drupal::messenger()->addStatus('10 RON to EUR:' . $value);
    }
    catch (UnavailableCurrency $e) {
      \Drupal::messenger()->addError($e->getMessage());
    }
    try {
      $value = $exchange->convert(10, 'EUR', 'BYN');
      \Drupal::messenger()->addStatus('10 EUR to BYN:' . $value);
    }
    catch (UnavailableCurrency $e) {
      \Drupal::messenger()->addError($e->getMessage());
    }
    try {
      $value = $exchange->convert(200.4557, 'BYN', 'RUB');
      \Drupal::messenger()->addStatus('200.4557 BYN to RUB:' . $value);
    }
    catch (UnavailableCurrency $e) {
      \Drupal::messenger()->addError($e->getMessage());
    }

    $config = $this->config('fixerio.settings');
    $cron = \Drupal::state()
      ->get('fixerio.rates_check', 0);

    /** @var \Drupal\Core\Datetime\DateFormatterInterface $formatter */
    $formatter = \Drupal::service('date.formatter');
    $message = $this->t('Next rates update: @next', [
      '@next' => $formatter->format($cron),
    ]);
    \Drupal::messenger()->addStatus($message);

    $form['plan'] = [
      '#type' => 'select',
      '#title' => $this->t('Plan'),
      '#description' => $this->t('Subscription plan on fixer.io'),
      '#options' => [
        'free' => $this->t('Free'),
        'basic' => $this->t('Basic', [], [
          'context' => 'fixerio',
        ]),
        'professional' => $this->t('Professional'),
        'professional_plus' => $this->t('Professional plus'),
      ],
      '#size' => 1,
      '#required' => TRUE,
      '#default_value' => $config->get('plan') ?? 'free',
    ];
    $form['api_access_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Access Key'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('api_access_key'),
      '#description' => $this->t('Attention! Use @var in settings.php to save your real API key. Current value: @value', [
        '@var' => '$config[\'fixerio.settings\'][\'api_access_key\']',
        '@value' => $this->configFactory()->get('fixerio.settings')->getOriginal('api_access_key'),
      ]),
    ];

    $form['refresh'] = [
      '#type' => 'select',
      '#title' => $this->t('Refresh'),
      '#description' => $this->t('Refresh interval'),
      '#options' => [
        '3600' => $this->t('Hourly'),
        '43200' => $this->t('12 hour'),
        '86400' => $this->t('Day'),
      ],
      '#default_value' => $config->get('refresh') ?? '3600',
    ];

    $form['base'] = [
      '#type' => 'select',
      '#title' => $this->t('Base currency'),
      '#description' => $this->t('Available for base plan and above.'),
      '#options' => $this->currenciesList(),
      '#size' => 1,
      '#default_value' => $config->get('base') ?? 'eur',
      '#states' => [
        'disabled' => [
          ':input[name="plan"]' => [
            'value' => 'free',
          ],
        ],
      ],
    ];

    $form['available_currencies'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Available currencies'),
      '#options' => $this->currenciesList(),
      '#default_value' => $config->get('available_currencies'),
      '#attributes' => [
        'class' => ['container-available-currencies'],
      ],
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $base = ($form_state->getValue('plan') == 'free') ?
      'eur' : $form_state->getValue('base');

    $this->config('fixerio.settings')
      ->set('plan', $form_state->getValue('plan'))
      ->set('base', $base)
      ->set('refresh', $form_state->getValue('refresh'))
      ->set('api_access_key', $form_state->getValue('api_access_key'))
      ->set('available_currencies', array_filter($form_state->getValue('available_currencies')))
      ->save();

    $this->api->latest();
  }

  /**
   * Helper function.
   *
   * @return array
   *   Options list.
   */
  private function currenciesList() {
    $options = [];
    $currencies = $this->api->symbols();
    foreach ($currencies as $code => $name) {
      $key = mb_strtolower($code);
      $options[$key] = $this->t('@name (@code)', [
        '@name' => $name,
        '@code' => $code,
      ]);
    }
    return $options;
  }

}
