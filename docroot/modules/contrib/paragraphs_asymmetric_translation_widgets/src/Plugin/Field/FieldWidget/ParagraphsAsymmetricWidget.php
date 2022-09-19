<?php

namespace Drupal\paragraphs_asymmetric_translation_widgets\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\paragraphs\Plugin\Field\FieldWidget\ParagraphsWidget;

/**
 * Extends the 'paragraphs' widget to add support for asymmetric translations.
 */
class ParagraphsAsymmetricWidget extends ParagraphsWidget {

  /**
   * {@inheritdoc}
   */
  protected function allowReferenceChanges() {
    return parent::allowReferenceChanges() || $this->fieldDefinition->isTranslatable();
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Remove all translatability clues, it makes no sense when running async.
    foreach (Element::children($element['subform']) as $field) {
      if (isset($element['subform'][$field]['widget']['#after_build']) && is_array($element['subform'][$field]['widget']['#after_build'])) {
        foreach ($element['subform'][$field]['widget']['#after_build'] as $index => $after_build) {
          if (in_array('addTranslatabilityClue', $after_build, TRUE)) {
            unset($element['subform'][$field]['widget']['#after_build'][$index]);
          }
        }
      }
    }

    return $element;
  }

}
