<?php
/**
 * @file
 * Contains Drupal\mymodule\Controller\SecondController
 */

namespace Drupal\mymodule\Controller;

use Drupal\Core\Controller\ControllerBase;

class SecondController extends ControllerBase {
  public function content() {
    return array(
        '#type' => 'markup',
        '#markup' => t('My second custom page'),
    );
  }
}
