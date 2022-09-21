<?php

namespace Drupal\custom\Controller;

use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;

class customController
{
  public function welcome()
  {

    $nids = \Drupal::entityQuery('node')->condition('type', 'banner')->execute();
    $nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);
    $header = array(
      'Node name',
      'Node ID',
      'Created date',
    );
    foreach ($nodes as $node) {
        $data[] = array(
        $node->title->value,
        $node->id(),
        date('Y-m-d h:i:s a', $node->created->value),
      );

    }
    $form[] = array(
      '#type' => 'textfield',
      '#title' => 'Enter emplyee number'

    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Filter'),
      '#submit' => array('submitForm'),
    );
    $form['#attributes'] = array('id' => array('submitForm'));

    $form[] = array(
      // '#markup' => 'Welcome to test Website.'
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $data
    );
    return $form;
  }
  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    dpr('here');
  }
}
