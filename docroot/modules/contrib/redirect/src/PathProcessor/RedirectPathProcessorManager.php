<?php

namespace Drupal\redirect\PathProcessor;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\PathProcessorManager;
use Drupal\path_alias\PathProcessor\AliasPathProcessor;
use Drupal\system\PathProcessor\PathProcessorFiles;
use Symfony\Component\HttpFoundation\Request;

/**
 * Redirect path processor manager.
 *
 * Extends PathProcessorManager to customize the processInbound logic.
 */
class RedirectPathProcessorManager extends PathProcessorManager implements RedirectPathProcessorManagerInterface {

  /**
   * {@inheritdoc}
   */
  public function addInbound(InboundPathProcessorInterface $processor, $priority = 0) {
    if ($this->applies($processor)) {
      parent::addInbound($processor, $priority);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getRedirectRequestPaths(Request $request) {
    $paths = [];

    // Do the inbound processing to get the source path.
    $path = $request->getPathInfo();
    $source_path = $this->processRedirectInbound($path, $request);
    $paths[] = trim($source_path, '/');

    // Add the aliased path.
    $alias_path = $this->processRedirectInbound($path, $request, TRUE);
    $paths[] = trim($alias_path, '/');

    return array_filter(array_unique($paths));
  }

  /**
   * Processes the inbound path.
   *
   * @param string $path
   *   The path to process, with a leading slash.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param bool $skip_alias
   *   Whether to skip AliasPathProcessor::processInbound.
   *
   * @return string
   *   The processed path.
   */
  protected function processRedirectInbound($path, Request $request, $skip_alias = FALSE) {
    $processors = $this->getInbound();
    foreach ($processors as $processor) {
      // Skip AliasPathProcessor::processInbound if specified.
      if ($skip_alias === TRUE && $processor instanceof AliasPathProcessor) {
        continue;
      }

      $path = $processor->processInbound($path, $request);
    }
    return $path;
  }

  /**
   * Returns whether this processor should be added or not.
   *
   * @param \Drupal\Core\PathProcessor\InboundPathProcessorInterface $processor
   *   The processor object to add.
   *
   * @return bool
   *   True if the processor should be added, false otherwise.
   */
  protected function applies(InboundPathProcessorInterface $processor) {
    // Skip PathProcessorFiles::processInbound().
    // Private files paths are split by the inbound path processor and the
    // relative file path is moved to the 'file' query string parameter. This
    // is because the route system does not allow an arbitrary amount of
    // parameters.
    if ($processor instanceof PathProcessorFiles) {
      return FALSE;
    }
    return TRUE;
  }

}
