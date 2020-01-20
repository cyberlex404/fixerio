<?php


namespace Drupal\fixerio;

/**
 * Class Rate.
 *
 * @package Drupal\fixerio
 */
class Rate extends \stdClass {

  /**
   * Rate value.
   *
   * @var float
   */
  public $rate;

  /**
   * Currency code.
   *
   * @var string
   */
  public $code;

  /**
   * Base currency code.
   *
   * @var string
   */
  public $base;

  /**
   * Rate constructor.
   *
   * @param null|string $base
   *   Base code.
   * @param null|string $code
   *   Currency code.
   */
  public function __construct($base = NULL, $code = NULL) {}

  /**
   * Return rate value.
   *
   * @return float
   *   Value
   */
  public function rate():float {
    return (float) $this->rate;
  }

}
