<?php

namespace Drupal\content_calendar;

use Drupal\content_calendar\Entity\ContentTypeConfig;
use Drupal\node\Entity\Node;

/**
 * Implements UserProfileImage class.
 */
class ContentService {

  /**
   * Type Configuration.
   *
   * @var int
   */
  private $contentTypeConfig;

  /**
   * ContentService constructor.
   */
  public function __construct() {
    $this->contentTypeConfig = ContentTypeConfig::loadMultiple();
  }

  /**
   * Get Content Configuration Type.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]|static[]
   *   Returns an static interface.
   */
  public function getContentTypeConfig() {
    return $this->contentTypeConfig;
  }

  /**
   * Get the recent content.
   */
  public function getRecentContent($limit) {
    $configs = $this->getContentTypeConfig();
    $types = [];

    if (is_array($configs)) {
      foreach ($configs as $config) {
        $types[] = $config->getOriginalId();
      }
    }

    if (empty($types)) {
      return [];
    }

    $ids = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', $types, 'IN')
      ->sort('created', 'DESC')
      ->range(0, $limit)
      ->execute();

    return Node::loadMultiple($ids);
  }

  /**
   * Get the following content.
   */
  public function getFollowingContent($limit) {
    $configs = $this->getContentTypeConfig();
    $types = [];

    if (is_array($configs)) {
      foreach ($configs as $config) {
        $types[] = $config->getOriginalId();
      }
    }

    if (empty($types)) {
      return [];
    }

    $ids = \Drupal::entityQuery('node')
      ->condition('status', 0)
      ->condition('type', $types, 'IN')
      ->condition('publish_on', NULL, 'IS NOT NULL')
      ->sort('publish_on', 'ASC')
      ->range(0, $limit)
      ->execute();

    return Node::loadMultiple($ids);
  }

}
