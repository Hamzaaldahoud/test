<?php

namespace Drupal\advanced_text_formatter;

use Drupal\Core\Security\TrustedCallbackInterface;

class AdvancedTextFormatterTokenTree implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['preRender'];
  }

  /**
   * Add available tokens link to text_format element.
   *
   * @param array $element
   *   Form element
   *
   * @return array
   *   Form element
   */
  public static function preRender($element) {
    $element['format']['show_token'] = array(
      '#type' => 'container',
      'link'  => array(
        '#markup' => _advanced_text_formatter_browse_tokens($element['#entity_type']),
      ),
      '#attributes' => array(
        'class' => array('clearfix'),
      ),
      '#weight' => 99,
    );

    return $element;
  }

}
