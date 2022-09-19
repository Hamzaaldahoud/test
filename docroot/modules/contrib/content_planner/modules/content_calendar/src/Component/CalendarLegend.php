<?php

namespace Drupal\content_calendar\Component;

/**
 * Implements CalendarLegend class.
 */
abstract class CalendarLegend {

  /**
   * {@inheritdoc}
   */
  public static function build(array $content_config_entities) {

    $build = [
      '#theme' => 'content_calendar_legend',
      '#content_type_configs' => $content_config_entities,
    ];

    return $build;

  }

}
