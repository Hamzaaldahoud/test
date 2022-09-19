<?php

namespace Drupal\content_kanban\Component;

use Drupal\content_kanban\Form\SettingsForm;
use Drupal\content_kanban\EntityTypeConfig;
use Drupal\content_planner\Component\BaseEntry;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Implements KanbanEntry class.
 *
 * @package Drupal\content_kanban\Component
 */
class KanbanEntry extends BaseEntry {

  /**
   * The entity object.
   *
   * @var object
   */
  protected $entity;

  /**
   * The entity type config.
   *
   * @var \Drupal\content_kanban\EntityTypeConfig
   */
  protected $entityTypeConfig;

  /**
   * The immutable config to store existing configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The content moderation status.
   *
   * @var string
   */
  protected $contentModerationStatus;

  /**
   * KanbanEntry constructor.
   *
   * @param object $entity
   *   The entity object.
   * @param string $content_moderation_status
   *   A string representing the current content moderation status.
   * @param \Drupal\content_kanban\EntityTypeConfig $entity_type_config
   *   The entity type config object.
   */
  public function __construct(
    $entity,
    $content_moderation_status,
    EntityTypeConfig $entity_type_config
  ) {
    $this->entity = $entity;
    $this->contentModerationStatus = $content_moderation_status;
    $this->entityTypeConfig = $entity_type_config;
    $this->config = \Drupal::config(SettingsForm::CONFIG_NAME);
  }

  /**
   * Builds the Kanban Entry output.
   */
  public function build() {
    $build = [];
    // Add time format to Entity.
    $datetime = new \DateTime();
    // Get the entity type and its keys.
    $entityType = \Drupal::entityTypeManager()->getStorage($this->entityTypeConfig->getEntityType());

    if ($entityType instanceof EntityStorageInterface) {

      $entityKeys = $entityType->getEntityType()->getKeys();
      $entityId = $entityKeys['id'];

      // Set needed info on the object.
      $this->entity->time = $datetime->format('H:i');
      $this->entity->entityLoaded = $entityType->load($this->entity->$entityId);

      // Get User Picture.
      $user_picture = $this->getUserPictureUrl($entityKeys['owner'] ?: $entityKeys['uid']);
      $build = [
        '#theme' => 'content_kanban_column_entry',
        '#entity' => $this->entity,
        '#entity_id' => $this->entity->$entityId,
        '#entity_type' => $this->entityTypeConfig->getEntityType(),
        '#entity_type_config' => $this->entityTypeConfig,
        '#user_picture' => $user_picture,
        '#workflow_state' => $this->contentModerationStatus,
        '#operation_links' => [
          'add' => $this->entity->entityLoaded->toUrl(),
          'edit' => $this->entity->entityLoaded->toUrl('edit-form'),
          'delete' => $this->entity->entityLoaded->toUrl('delete-form'),
        ],
        '#item_options' => [
          'background_color' => ($this->entity->entityLoaded->isPublished()) ? 'white' : '#fff4f4',
        ],
      ];
    }
    return $build;
  }

  /**
   * Gets the URL of the user picture.
   *
   * @param int $uid
   *   The user id.
   *
   * @return bool|string
   *   Returns the user picture url if any, FALSE otherwise.
   */
  protected function getUserPictureUrl($uid) {
    // If show user thumb is active.
    if ($this->config->get('show_user_thumb') && isset($this->entity->$uid)) {
      return $this->getUserPictureFromCache($this->entity->$uid, 'content_kanban_user_thumb');
    }

    return FALSE;
  }

}
