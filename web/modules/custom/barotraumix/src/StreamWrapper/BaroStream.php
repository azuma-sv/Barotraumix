<?php

/**
 * @file
 * Contains declaration of "baro://" stream wrapper.
 *
 * This filesystem will serve files which are downloaded by SteamCMD.
 */

namespace Drupal\barotraumix\StreamWrapper;

use Drupal;
use Drupal\Core\DrupalKernel;
use Drupal\Core\Site\Settings;
use Drupal\Core\StreamWrapper\LocalStream;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Component\Utility\UrlHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BaroStream.
 */
class BaroStream extends LocalStream {

  /**
   * {@inheritdoc}
   */
  public static function getType():int {
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
  public function getDirectoryPath():string {
    return static::basePath();
  }

  /**
   * {@inheritdoc}
   */
  public function getExternalUrl(): string {
    $path = str_replace('\\', '/', $this->getTarget());
    return $GLOBALS['base_url'] . '/' . static::basePath() . '/' . UrlHelper::encodePath($path);
  }

  /**
   * Returns the base path for baro://.
   *
   * If we have a setting for the baro:// scheme's path, we use that.
   * Otherwise, we build a reasonable default based on the site.path service if
   * it's available, or a default behavior based on the request.
   *
   * Note that this static method is used by \Drupal\system\Form\FileSystemForm,
   * so you should alter that form or substitute a different form if you change
   * the class providing the stream_wrapper.public service.
   *
   * The site path is injectable from the site.path service:
   * @code
   * $base_path = PublicStream::basePath(\Drupal::getContainer()->getParameter('site.path'));
   * @endcode
   *
   * @param null|string $site_path
   *   (optional) The site.path service parameter, which is typically the path
   *   to sites/* in a Drupal installation. This allows you to inject the site
   *   path using services from the caller. If omitted, this method will use the
   *   global service container or the kernel's default behavior to determine
   *   the site path.
   *
   * @return string
   *   The base path for baro:// typically sites/default/barotrauma.
   */
  public static function basePath(string $site_path = NULL):string {
    if ($site_path === NULL) {
      // Find the site path. Kernel service is not always available at this
      // point, but is preferred, when available.
      if (Drupal::hasService('kernel')) {
        $site_path = Drupal::getContainer()->getParameter('site.path');
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
