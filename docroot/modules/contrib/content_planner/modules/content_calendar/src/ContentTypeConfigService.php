<?php

namespace Drupal\content_calendar;

use Drupal\content_calendar\Entity\ContentTypeConfig;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Implements ContentTypeConfigService class  .
 */
class ContentTypeConfigService {

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new ContentTypeConfigService object.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Load all config entities.
   *
   * @return \Drupal\content_calendar\Entity\ContentTypeConfig[]
   *   Returns Drupal\content_calendar\Entity\ContentTypeConfig array.
   */
  public function loadAllEntities() {
    return ContentTypeConfig::loadMultiple();
  }

  /**
   * Load config entity by Content Type.
   *
   * @param string $content_type
   *   Content type name.
   *
   * @return bool|\Drupal\content_calendar\Entity\ContentTypeConfig|null|static
   *   Returns false or a static content calendar.
   */
  public function loadEntityByContentType($content_type) {

    if ($entity = ContentTypeConfig::load($content_type)) {
      return $entity;
    }

    return FALSE;
  }

  /**
   * Create new config entity.
   *
   * @param int $node_type
   *   The node type id.
   * @param string $label
   *   The label.
   * @param string $color
   *   The color.
   *
   * @return int
   *   Returns the number of the entity and save.
   */
  public function createEntity($node_type, $label, $color = '#0074bd') {

    $entity_build = [
      'id' => $node_type,
      'label' => $label,
      'color' => $color,
    ];

    $entity = ContentTypeConfig::create($entity_build);

    return $entity->save();
  }

}
