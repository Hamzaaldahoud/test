<?php

namespace Drupal\block_content\Plugin\Validation\Constraint;

use Drupal\block_content\BlockContentInterface;
use Drupal\Core\Entity\Plugin\Validation\Constraint\EntityChangedConstraintValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Validates the BlockContentEntityChanged constraint.
 */
class BlockContentEntityChangedConstraintValidator extends EntityChangedConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    // Don't validate "inline" (i.e. non-reusable) block content entities
    // as this prevents saving an update to the block form on a reverted host
    // entity's layout in layout builder. This is safe because inline blocks
    // are not actually saved until the whole layout is saved, in which case
    // Layout Builder forces a new revision for the block.
    // @see \Drupal\layout_builder\InlineBlockEntityOperations::handlePreSave.
    if ($entity instanceof BlockContentInterface && !$entity->isReusable()) {
      return;
    }
    parent::validate($entity, $constraint);
  }

}
