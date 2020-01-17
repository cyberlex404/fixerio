<?php

namespace Drupal\fixerio;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines an interface for currencies_plugin managers.
 */
interface CurrenciesPluginManagerInterface extends PluginManagerInterface {

  /**
   * Get currencies list.
   *
   * @return mixed
   */
  public function getCurrencies();

  /**
   * Get Options for form element.
   *
   * @return mixed
   *   Options list
   */
  public function currenciesOptionsList();

}
