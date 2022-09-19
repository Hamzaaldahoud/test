<?php

namespace Drupal\content_planner\Component;

use Drupal\content_planner\UserProfileImage;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;

/**
 * Base class for Content Planner components (e.g. CalendarEntry, KanbanEntry).
 *
 * @package Drupal\content_planner\Component
 */
class BaseEntry {

  /**
   * Drupal static method to retrieve the user picture url by user id.
   *
   * @param int $userId
   *   The user ID.
   * @param string $imageStyle
   *   The machine name of the image style.
   *
   * @return bool|string
   *   Returns the picture url if any, FALSE otherwise.
   */
  public function getUserPictureFromCache($userId, $imageStyle) {
    $pictureStyles = &drupal_static(__METHOD__, []);

    if (!isset($pictureStyles[$imageStyle][$userId])) {
      $styleUrl = FALSE;
      // If a user picture is not in the static cache, then create one.
      $user = User::load($userId);
      if ($user instanceof UserInterface) {
        $styleUrl = UserProfileImage::generateProfileImageUrl($user, $imageStyle);
      }
      $pictureStyles[$imageStyle][$userId] = $styleUrl;
    }

    return $pictureStyles[$imageStyle][$userId];
  }

}
