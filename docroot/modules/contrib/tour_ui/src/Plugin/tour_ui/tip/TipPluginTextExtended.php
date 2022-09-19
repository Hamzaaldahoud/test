<?php

namespace Drupal\tour_ui\Plugin\tour_ui\tip;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tour\Plugin\tour\tip\TipPluginText;
use Drupal\Component\Utility\Xss;

/**
 * This plugin override Tour\tip\TipPluginText to add UI methods.
 *
 * It should not appear as tour\tip plugin because tour_ui shouldn't be
 * installed on production. So this plugin will not be availble anymore once the
 * module will be installed.
 * The only goal of this plugin is to provide missing ui methods for default
 * tip text plugin.
 *
 * @Tip(
 *   id = "text_extended",
 *   title = @Translation("Text")
 * )
 */
class TipPluginTextExtended extends TipPluginText {

  /**
   * {@inheritdoc}
   *
   * TODO: Remove this method when
   * https://www.drupal.org/node/2851166#comment-11925707 will be commited.
   */
  public function getConfiguration() {
    $names = [
      'id',
      'plugin',
      'label',
      'weight',
      'selector',
      'body',
      'position',
    ];
    foreach ($names as $name) {
      $properties[$name] = $this->get($name);
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $id = $this->get('id');
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#required' => TRUE,
      '#default_value' => $this->get('label'),
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#machine_name' => [
        'exists' => '\Drupal\tour\Entity\Tour::load',
        'replace_pattern' => '[^a-z0-9-]+',
        'replace' => '-',
      ],
      '#default_value' => $id,
      '#disabled' => !empty($id),
    ];
    $form['plugin'] = [
      '#type' => 'value',
      '#value' => $this->get('plugin'),
    ];
    $form['weight'] = [
      '#type' => 'weight',
      '#title' => $this->t('Weight'),
      '#default_value' => $this->get('weight'),
      '#attributes' => [
        'class' => ['tip-order-weight'],
      ],
      '#delta' => 100,
    ];

    $form['selector'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Selector'),
      '#default_value' => $this->get('selector'),
      '#description' => $this->t('This can be any selector string or a DOM element (e.g,. .some .selector-path or #some-id). If you don’t specify the element will appear in the middle of the screen.'),
    ];

    $form['position'] = [
      '#type' => 'select',
      '#title' => $this->t('Position'),
      '#options' => [
        'auto' => $this->t('Auto'),
        'auto-start' => $this->t('Auto start'),
        'auto-end' => $this->t('Auto end'),
        'top' => $this->t('Top'),
        'top-start' => $this->t('Top start'),
        'top-end' => $this->t('Top end'),
        'bottom' => $this->t('Bottom'),
        'bottom-start' => $this->t('Bottom start'),
        'bottom-end' => $this->t('Bottom end'),
        'right' => $this->t('Right'),
        'right-start' => $this->t('Right start'),
        'right-end' => $this->t('Right end'),
        'left' => $this->t('Left'),
        'left-start' => $this->t('Left start'),
        'left-end' => $this->t('Left end'),
      ],
      '#default_value' => $this->get('position'),
    ];
    $tags = Xss::getAdminTagList();
    $form['body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Body'),
      '#required' => TRUE,
      '#default_value' => $this->get('body'),
      '#description' => $this->t('You could use the following tags: %s', ['%s' => implode(', ', $tags)]),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

}
