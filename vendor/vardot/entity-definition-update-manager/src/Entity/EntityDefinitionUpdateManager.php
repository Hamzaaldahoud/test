<?php

namespace Vardot\Entity;

use Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface;
use Drupal\Core\Entity\Schema\EntityStorageSchemaInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Entity Definition Update Manager.
 *
 * Customized class from the Devel Entity Updates module
 * https://www.drupal.org/project/devel_entity_updates
 * Development version of the entity definition update manager.
 */
class EntityDefinitionUpdateManager {

  /**
   * Applies all the detected valid changes.
   */
  public function applyUpdates() {

    $complete_change_list = \Drupal::entityDefinitionUpdateManager()->getChangeList();

    if ($complete_change_list) {
      // In case there are changes, explicitly invalidate caches.
      \Drupal::entityTypeManager()->clearCachedDefinitions();
      \Drupal::service('entity_field.manager')->clearCachedFieldDefinitions();
    }

    foreach ($complete_change_list as $entity_type_id => $change_list) {
      // Process entity type definition changes before storage definitions ones
      // this is necessary when you change an entity type from non-revisionable
      // to revisionable and at the same time add revisionable fields to the
      // entity type.
      if (!empty($change_list['entity_type'])) {
        $this->doEntityUpdate($change_list['entity_type'], $entity_type_id);
      }

      // Process field storage definition changes.
      if (!empty($change_list['field_storage_definitions'])) {
        $storage_definitions = \Drupal::service('entity_field.manager')->getFieldStorageDefinitions($entity_type_id);
        $original_storage_definitions = \Drupal::service('entity.last_installed_schema.repository')->getLastInstalledFieldStorageDefinitions($entity_type_id);

        foreach ($change_list['field_storage_definitions'] as $field_name => $change) {
          $storage_definition = isset($storage_definitions[$field_name]) ? $storage_definitions[$field_name] : NULL;
          $original_storage_definition = isset($original_storage_definitions[$field_name]) ? $original_storage_definitions[$field_name] : NULL;
          $this->doFieldUpdate($change, $storage_definition, $original_storage_definition);
        }
      }
    }
  }

  /**
   * Performs an entity type definition update.
   *
   * @param string $op
   *   The operation to perform, either static::DEFINITION_CREATED or
   *   static::DEFINITION_UPDATED.
   * @param string $entity_type_id
   *   The entity type ID.
   */
  private function doEntityUpdate($op, $entity_type_id) {
    $entity_type = \Drupal::entityTypeManager()->getDefinition($entity_type_id);
    switch ($op) {
      case EntityDefinitionUpdateManagerInterface::DEFINITION_CREATED:
        \Drupal::service('entity_type.listener')->onEntityTypeCreate($entity_type);
        break;

      case EntityDefinitionUpdateManagerInterface::DEFINITION_UPDATED:
        $original = \Drupal::service('entity.last_installed_schema.repository')->getLastInstalledDefinition($entity_type_id);
        $storage = \Drupal::entityTypeManager()->getStorage($entity_type->id());
        if ($storage instanceof EntityStorageSchemaInterface && $storage->requiresEntityDataMigration($entity_type, $original)) {
          throw new \InvalidArgumentException('The entity schema update for the ' . $entity_type->id() . ' entity type requires a data migration.');
        }
        $field_storage_definitions = \Drupal::service('entity.last_installed_schema.repository')->getFieldStorageDefinitions($entity_type_id);
        $original_field_Storage_definitions = \Drupal::service('entity.last_installed_schema.repository')->getLastInstalledFieldStorageDefinitions($entity_type_id);
        \Drupal::service('entity_type.listener')->onFieldableEntityTypeUpdate($entity_type, $original, $field_storage_definitions, $original_field_Storage_definitions);
        break;
    }
  }

  /**
   * Performs a field storage definition update.
   *
   * @param string $op
   *   The operation to perform, possible values are:
   *   - EntityDefinitionUpdateManagerInterface::DEFINITION_CREATED
   *   - EntityDefinitionUpdateManagerInterface::DEFINITION_UPDATED
   *   - EntityDefinitionUpdateManagerInterface::DEFINITION_DELETED
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface|null $storage_definition
   *   (optional) The new field storage definition. Defaults to none.
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface|null $original_storage_definition
   *   (optional) The original field storage definition. Defaults to none.
   */
  private function doFieldUpdate($op, FieldStorageDefinitionInterface $storage_definition = NULL, FieldStorageDefinitionInterface $original_storage_definition = NULL) {
    switch ($op) {
      case EntityDefinitionUpdateManagerInterface::DEFINITION_CREATED:
        \Drupal::service('field_storage_definition.listener')->onFieldStorageDefinitionCreate($storage_definition);
        break;

      case EntityDefinitionUpdateManagerInterface::DEFINITION_UPDATED:
        if ($storage_definition && $original_storage_definition) {
          \Drupal::service('field_storage_definition.listener')->onFieldStorageDefinitionUpdate($storage_definition, $original_storage_definition);
        }
        break;

      case EntityDefinitionUpdateManagerInterface::DEFINITION_DELETED:
        if ($original_storage_definition) {
          \Drupal::service('field_storage_definition.listener')->onFieldStorageDefinitionDelete($original_storage_definition);
        }
        break;
    }
  }

}