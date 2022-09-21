<?php
namespace Drupal\custom\Controller;
class customController {
  public function welcome() {

    return array(
      '#markup' => 'Welcome to test Website.'
    );
  }
}