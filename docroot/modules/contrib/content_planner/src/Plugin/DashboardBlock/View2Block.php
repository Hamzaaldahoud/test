<?php

namespace Drupal\content_planner\Plugin\DashboardBlock;

/**
 * Provides a view block for Content Planner Dashboard.
 *
 * @DashboardBlock(
 *   id = "view_2_block",
 *   name = @Translation("Views Widget 2")
 * )
 */
class View2Block extends ViewBlockBase {

  /**
   * ID for the block.
   *
   * @var string
   */
  protected $blockID = 'view_2';

}
