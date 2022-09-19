<?php

namespace Drupal\content_planner;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\content_moderation\Entity\ContentModerationState;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides functions for loading information related to content moderation.
 */
class ContentModerationService {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new ContentModerationService object.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager
  ) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Gets the Content Moderation Entities.
   *
   * @param string $workflow
   *   The workflow ID.
   * @param array $filters
   *   An array with the filters.
   * @param array $entities
   *   An array with the entities.
   *
   * @return \Drupal\content_moderation\Entity\ContentModerationState[]
   *   Returns an array with the content moderation states for the given
   *   workflow.
   */
  public function getEntityContentModerationEntities($workflow, array $filters = [], array $entities = []) {
    $result = [];
    try {
      $query = $this->entityTypeManager->getStorage('content_moderation_state')->getQuery();
      if (!empty(array_keys($entities))) {
        $query->condition('workflow', $workflow);
        $query->condition('content_entity_type_id', array_keys($entities), 'in');
      }

      // Moderation state filter.
      if (array_key_exists('moderation_state', $filters) && $filters['moderation_state']) {
        $query->condition('moderation_state', $filters['moderation_state']);
      }

      // User ID filter.
      if (array_key_exists('uid', $filters) && $filters['uid']) {
        $query->condition('uid', $filters['uid']);
      }

      // User ID filter.
      $result = $query->execute();
    }
    catch (InvalidPluginDefinitionException $e) {
      watchdog_exception('content_planner', $e);
    }
    catch (PluginNotFoundException $e) {
      watchdog_exception('content_planner', $e);
    }
    if ($result) {
      return ContentModerationState::loadMultiple($result);
    }

    return $result;
  }

  /**
   * Gets the entity IDs from Content Moderation entities.
   *
   * @param string $workflow
   *   The workflow id.
   * @param array $filters
   *   An array with the filters.
   * @param array $entities
   *   An array with the entities.
   *
   * @return array
   *   Returns an array with the entity ids.
   */
  public function getEntityIdsFromContentModerationEntities($workflow, array $filters = [], array $entities = []) {
    $entityIds = [];

    if ($content_moderation_states = $this->getEntityContentModerationEntities($workflow, $filters, $entities)) {
      foreach ($content_moderation_states as $content_moderation_state) {

        // Get property.
        $content_entity_id_property = $content_moderation_state->content_entity_id;

        // Get value.
        $content_entity_id_value = $content_entity_id_property->getValue();
        $entity_type_id_value = $content_moderation_state->get('content_entity_type_id')->getValue();
        // Get the entity type id.
        $entity_type_id = $entity_type_id_value[0]['value'];
        // Build the ids array with entity type as key.
        $entityIds[$entity_type_id][] = $content_entity_id_value[0]['value'];
      }

    }
    return $entityIds;
  }

}
