<?php


namespace Drupal\fixerio;


class Rate extends \stdClass {

  public $rate;

  public $code;

  public $base;

  public function __construct($base = NULL, $code = NULL) {

  }

  public function rate():float {
    return (float) $this->rate;
  }

}
