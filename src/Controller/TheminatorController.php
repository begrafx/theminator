<?php
/**
 * @file
 * Contains Drupal\theminator\Controller\TheminatorController
 */


namespace Drupal\theminator\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for block example routes.
 */
class TheminatorController extends ControllerBase {

  public function fieldInstanceConfig() {
    return array(
      '#type' => 'markup',
      '#markup' => t('Hello world'),
    );
  }
}
