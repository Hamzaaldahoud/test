<?php

namespace Drupal\varbase_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Settings Form for Varbase API.
 *
 * @category Class
 * @package Varbase API
 */

/**
 * The settings form for controlling Content API's behavior.
 */
class VarbaseApiSettingsForm extends ConfigFormBase implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates a new NodeField instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager interface.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['varbase_api.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'varbase_api_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('varbase_api.settings');

    $form['entity_json'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Expose a "View JSON" link in entity operations'),
      '#default_value' => $config->get('entity_json'),
    ];
    $form['bundle_docs'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Expose a "View API Documentation" link in bundle entity operations'),
      '#default_value' => $config->get('bundle_docs'),
    ];

    $entityDefinitions = $this->entityTypeManager->getDefinitions();

    if (isset($entityDefinitions)
      && is_countable($entityDefinitions)
      && count($entityDefinitions)) {

      $entityTypesList = [];
      foreach ($entityDefinitions as $entityName => $entityDefinition) {
        $entityTypesList[$entityName] = (string) $entityDefinition->getLabel();
      }

      $form['auto_enabled_entity_types'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Auto Enabled JSON:API Endpoints for Entity Types'),
        '#options' => $entityTypesList,
        '#default_value' => (array) $config->get('auto_enabled_entity_types'),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $auto_enabled_entity_types_ids = [];
    $auto_enabled_entity_types = (array) $form_state->getValue('auto_enabled_entity_types');
    if (isset($auto_enabled_entity_types)
      && is_countable($auto_enabled_entity_types)
      && count($auto_enabled_entity_types) > 0) {

      foreach ($auto_enabled_entity_types as $entity_type_id => $entity_type_vlaue) {
        if ($entity_type_vlaue !== 0) {
          $auto_enabled_entity_types_ids[$entity_type_id] = $entity_type_id;
        }
      }

      $auto_enabled_entity_types_ids = array_keys($auto_enabled_entity_types_ids);
    }

    $this->config('varbase_api.settings')
      ->set('entity_json', (bool) $form_state->getValue('entity_json'))
      ->set('bundle_docs', (bool) $form_state->getValue('bundle_docs'))
      ->set('auto_enabled_entity_types', $auto_enabled_entity_types_ids)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
