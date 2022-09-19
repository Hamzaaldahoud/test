<?php

namespace Drupal\content_planner\Plugin\DashboardBlock;

use Drupal\content_planner\DashboardBlockBase;
use Drupal\content_planner\UserProfileImage;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a user block for Content Planner Dashboard.
 *
 * @DashboardBlock(
 *   id = "user_block",
 *   name = @Translation("User Widget")
 * )
 */
class UserBlock extends DashboardBlockBase {

  use StringTranslationTrait;

  /**
   * Builds the render array for a dashboard block.
   *
   * @return array
   *   The markup for the dashboard block.
   */
  public function build() {
    $config = $this->getConfiguration();

    $users = $this->getUsers($config);

    if ($users) {
      $user_data = [];

      foreach ($users as $user) {

        $roles = array_map(
          function ($role) {
            if ($role != 'authenticated') {
              /** @var \Drupal\user\RoleInterface $role_entity */
              $role_entity = $this->entityTypeManager->getStorage('user_role')->load($role);
              return $role_entity ? $role_entity->label() : NULL;
            }
          },
          $user->getRoles()
        );

        $user_data[] = [
          'name' => $user->label(),
          'image' => UserProfileImage::generateProfileImageUrl($user, 'content_planner_user_block_profile_image'),
          'roles' => implode(', ', array_filter($roles)),
          'content_count' => $this->getUserContentCount($user->id()),
          'content_kalendertag_count' => $this->getUserContentWorkflowCount($user->id(), 'am_kalendertag_publizieren'),
          'content_draft_count' => $this->getUserContentWorkflowCount($user->id(), 'draft'),
        ];

      }

      return [
        '#theme' => 'content_planner_dashboard_user_block',
        '#users' => $user_data,
      ];
    }

    return [];
  }

  /**
   * Loads the users with roles set in config.
   *
   * @return \Drupal\user\Entity\User[]
   *   Array of loaded user entities.
   */
  protected function getUsers() {

    $config = $this->getConfiguration()['plugin_specific_config'];
    if (!isset($config['roles'])) {
      return [];
    }

    $configured_roles = $config['roles'];
    $display_blocked = $config['blocked'] ?? true;

    $query = \Drupal::entityQuery('user');
    $query->condition('roles', array_values($configured_roles), 'in');
    $query->sort('access', 'desc');
    if (!$display_blocked) {
      $query->condition('status', 1);
    }

    $result = $query->execute();
    if ($result === []) {
      return [];
    }

    return User::loadMultiple($result);
  }

  /**
   * Get content count for a given user.
   *
   * @param int $user_id
   *   The user id to load the content count for.
   *
   * @return int
   *   The content count for the given user id.
   */
  protected function getUserContentCount($user_id) {

    $query = \Drupal::database()->select('node_field_data', 'nfd');
    $query->fields('nfd', ['nid']);
    $query->condition('nfd.uid', $user_id);
    $query->countQuery();

    $result = $query->execute();

    $result->allowRowCount = TRUE;

    $count = $result->rowCount();

    if ($count) {
      return $count;
    }

    return 0;
  }

  /**
   * Get content count for a given user based on workflow status.
   *
   * @param int $user_id
   *   The user id the get the workflow count for.
   * @param string $moderation_state
   *   The moderation state the get the count for.
   *
   * @return int
   *   The content count for the given user and the given moderation state.
   */
  public function getUserContentWorkflowCount($user_id, $moderation_state) {
    $content_moderation_service = \Drupal::service('content_planner.content_moderation_service');

    $filters = [
      'uid' => $user_id,
      'moderation_state' => $moderation_state,
    ];
    $nids = $content_moderation_service->getEntityIdsFromContentModerationEntities('netnode', $filters);

    return count($nids);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigSpecificFormFields(FormStateInterface &$form_state,
                                              Request &$request,
                                              array $block_configuration) {

    $form = [];

    // Build Role selection box.
    $form['roles'] = $this->buildRoleSelectBox($form_state, $request, $block_configuration);

    $form['blocked'] = [
      '#type' => 'checkbox',
      '#title' => t('Display blocked users'),
      '#default_value' => $block_configuration['plugin_specific_config']['blocked'] ?? true,
    ];

    return $form;
  }

  /**
   * Build Role select box.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param \Symfony\Component\HttpFoundation\Request|null $request
   *   The current request.
   * @param array $block_configuration
   *   The block configuration.
   *
   * @return array
   *   The roles checkboxes.
   */
  protected function buildRoleSelectBox(FormStateInterface &$form_state,
                                        Request &$request,
                                        array $block_configuration) {

    // Get Roles.
    $roles = Role::loadMultiple();

    $roles_options = [];

    foreach ($roles as $role_id => $role) {

      if (in_array($role_id, ['anonymous'])) {
        continue;
      }

      $roles_options[$role_id] = $role->label();
    }

    $default_value = (isset($block_configuration['plugin_specific_config']['roles'])) ? $block_configuration['plugin_specific_config']['roles'] : [];

    return [
      '#type' => 'checkboxes',
      '#title' => $this->t('Which Roles to display'),
      '#description' => $this->t('Select which Roles should be displayed in the block.'),
      '#required' => TRUE,
      '#options' => $roles_options,
      '#default_value' => $default_value,
    ];
  }

}
