<?php

namespace Drupal\redirect\PathProcessor;

use Symfony\Component\HttpFoundation\Request;

/**
 * Defines an interface for redirect path processor manager classes.
 */
interface RedirectPathProcessorManagerInterface {

  /**
   * Get the paths that will be used to find matching redirects.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return array
   *   An array of paths.
   */
  public function getRedirectRequestPaths(Request $request);

}
