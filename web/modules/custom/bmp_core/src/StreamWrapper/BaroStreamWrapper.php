<?php

/**
 * @file
 * Contains declaration of BaroStreamWrapper.
 */

namespace Drupal\bmp_core\StreamWrapper;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\DrupalKernel;
use Drupal\Core\Site\Settings;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BaroStreamWrapper.
 */
class BaroStreamWrapper extends PublicStream {

  /**
   * {@inheritdoc}
   */
  public static function getType() {
    return StreamWrapperInterface::LOCAL_NORMAL;
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return t('Barotrauma Source Files');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): string {
    return t('Contain all apps downloaded from Steam.');
  }

  /**
   * {@inheritdoc}
   */
  public function getDirectoryPath() {
    return static::basePath();
  }

  /**
   * {@inheritdoc}
   */
  public function getExternalUrl(): string {
    $path = str_replace('\\', '/', $this->getTarget());
    return static::baseUrl() . '/' . UrlHelper::encodePath($path);
  }

  /**
   * {@inheritdoc}
   */
  public static function basePath($site_path = NULL) {
    if ($site_path === NULL) {
      // Find the site path. Kernel service is not always available at this
      // point, but is preferred, when available.
      if (\Drupal::hasService('kernel')) {
        $site_path = \Drupal::getContainer()->getParameter('site.path');
      }
      else {
        // If there is no kernel available yet, we call the static
        // findSitePath().
        $site_path = DrupalKernel::findSitePath(Request::createFromGlobals());
      }
    }
    return Settings::get('file_barotrauma_path', $site_path . '/barotrauma');
  }

}
