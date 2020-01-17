<?php

namespace Drupal\fixerio;

/**
 * Interface ExchangeInterface.
 */
interface ExchangeInterface {

  /**
   * Converts the amount to the specified currency.
   *
   * @param float $value
   *   Amount in current currency.
   * @param string $source
   *   Current Currency Code.
   * @param string $target
   *   Currency code for conversion.
   *
   * @throws \Drupal\fixerio\Exception\UnavailableCurrency
   *   UnavailableCurrency.
   *
   * @return float
   *   Converted value
   */
  public function convert(float $value, string $source, string $target):float;

}
