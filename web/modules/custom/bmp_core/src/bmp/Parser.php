<?php
/**
 * @file
 * Source code of the service "bmp_core.core".
 *
 * @todo: Big refactoring.
 */

namespace Drupal\bmp_core\BMP;

use Drupal\Core\File\FileSystemInterface;

/**
 * Class Parser to parse Barotrauma XML files.
 */
class Parser {

  /**
   * @var int $appId - Application ID to parse.
   */
  public $appId;

  /**
   * @var int $buildId - Build ID to parse.
   */
  public $buildId;

  /**
   * @var FileSystemInterface $fileSystem - File system service.
   * @todo: Improve documentation and declaration.
   */
  protected FileSystemInterface $fileSystem;

  /**
   * Class constructor.
   */
  public function __construct(int $appId, int $buildId) {
    $this->appId = $appId;
    $this->buildId = $buildId;
    /** @var \Drupal\Core\File\FileSystem $fileSystem */
    $this->fileSystem = \Drupal::service('file_system');
  }

  /**
   * Method.
   */
  public function contentPackage() {
    static $contentPackage;
    // Load XML if needed.
    if (!isset($contentPackage)) {
      $path = 'Content/ContentPackages/Vanilla.xml';
      $uri = $this->prepareUri($path);
//      $dirStatus = $this->fileSystem->prepareDirectory($uri, \Drupal\Core\File\FileSystemInterface::MODIFY_PERMISSIONS);
//      $chmod = $this->fileSystem->chmod($uri,0755);
      $realPath = $this->fileSystem->realpath($uri);
      if (/*$dirStatus && $chmod && */$realPath) {
        // @todo: Errors handling.
        $content = file_get_contents($realPath);
        return new \SimpleXMLElement($content);
      }
    }
    return NULL;
  }

  /**
   * Method.
   */
  protected function prepareUri($path = NULL) {
    $uri = 'baro://' . $this->appId . '/' . $this->buildId;
    if (isset($path)) {
      $uri .= '/' . $path;
    }
    return $uri;
  }

}
