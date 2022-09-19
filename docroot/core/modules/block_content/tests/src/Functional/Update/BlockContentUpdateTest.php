<?php

namespace Drupal\Tests\block_content\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests update functions for the Block Content module.
 *
 * @group Update
 */
class BlockContentUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-8.8.0.bare.standard.php.gz',
    ];
  }

  /**
   * Test updated block content constraints.
   */
  public function testBlockContentConstraintsUpdate() {
    $entity_definition_update_manager = \Drupal::entityDefinitionUpdateManager();

    $entity_type = $entity_definition_update_manager->getEntityType('block_content');
    $constraints = $entity_type->getConstraints();
    $this->assertArrayHasKey('EntityChanged', $constraints);
    $this->assertArrayNotHasKey('BlockContentEntityChanged', $constraints);

    $this->runUpdates();

    $entity_type = $entity_definition_update_manager->getEntityType('block_content');
    $constraints = $entity_type->getConstraints();
    $this->assertArrayNotHasKey('EntityChanged', $constraints);
    $this->assertArrayHasKey('BlockContentEntityChanged', $constraints);
  }

}
