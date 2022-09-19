<?php

namespace Drupal\content_calendar\Component;

use Drupal\content_calendar\DateTimeHelper;
use Drupal\content_calendar\Entity\ContentTypeConfig;
use Drupal\content_calendar\Form\SettingsForm;
use Drupal\content_planner\Component\BaseEntry;

/**
 * Implements CalendarEntry class.
 *
 * @package Drupal\content_calendar\Component
 */
class CalendarEntry extends BaseEntry {

  /**
   * Desired months to be rendered.
   *
   * @var int
   */
  protected $month;

  /**
   * Desired year to be rendered.
   *
   * @var int
   */
  protected $year;

  /**
   * The content type config.
   *
   * @var \Drupal\content_calendar\Entity\ContentTypeConfig
   */
  protected $contentTypeConfig;

  /**
   * The node.
   *
   * @var object
   */
  protected $node;

  /**
   * The immutable config to store existing configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * CalendarEntry constructor.
   *
   * @param int $month
   *   The month to display in the calendar.
   * @param int $year
   *   The year to display in the calendar.
   * @param \Drupal\content_calendar\Entity\ContentTypeConfig $content_type_config
   *   Content config type.
   * @param object $node
   *   Node Object.
   */
  public function __construct(
    $month,
    $year,
    ContentTypeConfig $content_type_config,
    \stdClass $node
  ) {
    $this->month = $month;
    $this->year = $year;
    $this->contentTypeConfig = $content_type_config;
    $this->node = $node;

    $this->config = \Drupal::config(SettingsForm::CONFIG_NAME);
  }

  /**
   * Get Node ID.
   *
   * @return mixed
   *   Return id of the node.
   */
  public function getNodeId() {
    return $this->node->nid;
  }

  /**
   * Get the relevant date for the current node.
   *
   * When the Scheduler date is empty, then take the creation date.
   *
   * @return int
   *   Return published or created date.
   */
  public function getRelevantDate() {

    if ($this->node->publish_on) {
      return $this->node->publish_on;
    }

    return $this->node->created;
  }

  /**
   * Format creation date as MySQL Date only.
   *
   * @return string
   *   Return datatime formated.
   */
  public function formatSchedulingDateAsMySqlDateOnly() {

    $datetime = DateTimeHelper::convertUnixTimestampToDatetime($this->node->created);

    return $datetime->format(DateTimeHelper::FORMAT_MYSQL_DATE_ONLY);
  }

  /**
   * Build.
   *
   * @return array
   *   Returns an builded array.
   */
  public function build() {

    // Get User Picture.
    $user_picture = $this->getUserPictureUrl();

    if ($this->node->publish_on) {
      $this->node->scheduled = TRUE;
    }
    else {
      $this->node->scheduled = FALSE;
    }

    // Add time to node object.
    $this->node->created_on_time = DateTimeHelper::convertUnixTimestampToDatetime($this->node->created)->format('H:i');

    // Build options.
    $options = $this->buildOptions();

    if (\Drupal::currentUser()->hasPermission('manage content calendar')) {
      $this->node->editoptions = TRUE;
    }

    if (\Drupal::currentUser()->hasPermission('manage own content calendar')) {
      if ($this->node->uid == \Drupal::currentUser()->id()) {
        $this->node->editoptions = TRUE;
      }
    }

    $build = [
      '#theme' => 'content_calendar_entry',
      '#node' => $this->node,
      '#node_type_config' => $this->contentTypeConfig,
      '#month' => $this->month,
      '#year' => $this->year,
      '#user_picture' => $user_picture,
      '#options' => $options,
    ];

    return $build;
  }

  /**
   * Build options before rendering.
   *
   * @return array
   *   Returns an array with the options.
   */
  protected function buildOptions() {

    $options = [];

    // Background color for unpublished content.
    $options['bg_color_unpublished_content'] = $this->config->get('bg_color_unpublished_content');

    return $options;
  }

  /**
   * Get the URL of the user picture.
   *
   * @return bool|string
   *   Returns false or string.
   */
  protected function getUserPictureUrl() {

    // If show user thumb is active.
    if ($this->config->get('show_user_thumb')) {
      return $this->getUserPictureFromCache($this->node->uid, 'content_calendar_user_thumb');
    }

    return FALSE;
  }

}
