<?php

namespace Drupal\content_calendar\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements CalenderOverviewFilterForm class.
 */
class CalenderOverviewFilterForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'calendar_overview_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $params = []) {

    // Add Calendar select box.
    $this->addCalendarYearSelectBox($form, $form_state, $params);

    $this->addJumpLinks($form, $form_state, $params);

    return $form;
  }

  /**
   * Add Calendar select box.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The current state of the form.
   * @param array $params
   *   Array params.
   */
  protected function addCalendarYearSelectBox(array &$form, FormStateInterface &$formState, array $params) {

    // Date range.
    $year_range = range(($params['current_year'] - 3), ($params['current_year'] + 3));

    $years = array_combine($year_range, $year_range);

    $form['calendar_year'] = [
      '#type' => 'select',
      '#options' => $years,
      '#required' => TRUE,
      '#default_value' => $params['selected_year'],
    ];

  }

  /**
   * Add jump links.
   */
  protected function addJumpLinks(array &$form, FormStateInterface &$formState, $params) {

    $months = [
      1 => $this->t('Jan'),
      2 => $this->t('Feb'),
      3 => $this->t('Mar'),
      4 => $this->t('Apr'),
      5 => $this->t('May'),
      6 => $this->t('Jun'),
      7 => $this->t('Jul'),
      8 => $this->t('Aug'),
      9 => $this->t('Sept'),
      10 => $this->t('Oct'),
      11 => $this->t('Nov'),
      12 => $this->t('Dec'),
    ];

    $form['jump_links'] = [
      '#theme' => 'content_calendar_jump_links',
      '#months' => $months,
      '#year' => $params['selected_year'],
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

}
