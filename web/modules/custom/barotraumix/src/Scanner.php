<?php

/**
 * @file
 * Contains a functionality to scan Barotrauma source files.
 *
 * Mostly this class helps to determine and create proper parsers to explore source files of barotrauma.
 */

namespace Drupal\barotraumix;

use Drupal\barotraumix\BaroEntity\RawNodeData;
use Drupal\Core\File\FileSystemInterface;
use Exception;

/**
 * Class Scanner.
 */
class Scanner {

  /**
   * @var int $appId - ID of the app in Steam.
   */
  protected int $appId;

  /**
   * @var int $buildId - Build id of the app in Steam.
   */
  protected int $buildId;

  /**
   * @var FileSystemInterface $fileSystem - File system service.
   */
  protected FileSystemInterface $fileSystem;

  /**
   * @var null|string $type - Type of the application which we are attempting to scan.
   */
  protected null|string $type;

  /**
   * @var null|string $parserClass - Class to use as parser for this application.
   */
  protected null|string $parserClass;

  /**
   * @var array $contentPackages - Array with parsers for content packages.
   */
  protected array $contentPackages = [];

  /**
   * Class constructor.
   *
   * @param int $appId - ID of the app in Steam.
   * @param int $buildId - Build id of the app in Steam.
   * @param FileSystemInterface $fileSystem - File system service.
   */
  public function __construct(int $appId, int $buildId, FileSystemInterface $fileSystem) {
    $this->appId = $appId;
    $this->buildId = $buildId;
    $this->fileSystem = $fileSystem;
    $this->init();
  }

  /**
   * Return type of the application which we are attempting to scan.
   *
   * @return string|null
   */
  public function type():null|string {
    return $this->type;
  }

  /**
   * Check if current application is Barotrauma game.
   *
   * @return bool
   */
  public function isGame():bool {
    return $this->type == 'game';
  }

  /**
   * Check if current application is Barotrauma mod.
   *
   * @return bool
   */
  public function isMod():bool {
    return $this->type == 'mod';
  }

  /**
   * Returns application ID.
   *
   * @return int
   */
  public function appId():int {
    return $this->appId;
  }

  /**
   * Returns build id of the application.
   *
   * @return int
   */
  public function buildId():int {
    return $this->buildId;
  }

  /**
   * Return array of data of the content package.
   *
   * @return RawNodeData
   */
  public function contentPackage():RawNodeData {
    // Get content package name.
    $name = $this->primaryContentPackage();
    if (isset($this->contentPackages[$name])) {
      return $this->contentPackages[$name]->data();
    }
    // Ensure that content package file is reachable.
    $uri = $this->uriForContentPackage($name);
    $filename = $this->fileSystem->getDestinationFilename($uri, FileSystemInterface::EXISTS_ERROR);
    if (!empty($filename)) {
      $message = "Unable to locate content package '$name' of the app: $this->appId (build id: $this->buildId)";
      throw new Exception($message);
    }
    // Check if current file really contains a content package.
    /** @var \Drupal\barotraumix\Parser\ParserClassic $parser - At current moment we have only one parser. */
    $parser = new $this->parserClass($uri, $this->fileSystem);
    if ($parser->name() != 'ContentPackage') {
      throw new Exception("Attempt to create content package from wrong file: $uri");
    }
    $this->contentPackages[$name] = $parser;
    // Create BaroEntity.
    return $parser->data();
  }

  /**
   * Get array of application assets.
   *
   * @return array
   */
  public function assets():array {
    $contentPackage = $this->contentPackage();
    return $contentPackage->children();
  }

  /**
   * Returns URI for content package of Barotrauma.
   *
   * @param null|string $package
   *  Name of file (without extension) of content package to use.
   *  Leave this parameter black to use default content package of this application.
   *
   * @return null|string
   */
  public function uriForContentPackage(null|string $package = NULL):null|string {
    $uri = NULL;
    // Set default value for content package name.
    if (!isset($package)) {
      $package = $this->primaryContentPackage();
    }
    // Attempt to get URI.
    if ($this->isGame()) {
      $uri = $this->prepareUri() . "/Content/ContentPackages/$package.xml";
    }
    if ($this->isMod()) {
      $uri = $this->prepareUri() . "/$package.xml";
    }
    return $uri;
  }

  /**
   * Attempt to initialize application type.
   *
   * @todo: Look for better way to figure what is game and what is mod. This method is not reliable.
   *
   * @return void
   */
  protected function init():void {
    $uri = $this->prepareUri();
    // Attempt to find filelist.xml - primary mod file.
    $filename = $this->fileSystem->getDestinationFilename($uri . '/filelist.xml', FileSystemInterface::EXISTS_ERROR);
    if (empty($filename)) {
      $this->type = 'mod';
    }
    // Attempt to find Vanilla Content package.
    $filename = $this->fileSystem->getDestinationFilename($uri . '/Content/ContentPackages/Vanilla.xml', FileSystemInterface::EXISTS_ERROR);
    if (empty($filename)) {
      $this->type = 'game';
    }
    // Assign parser class. At current moment we have only one.
    // New Parser might appear when Barotrauma will make significant changes in their files and their structure.
    if (isset($this->type)) {
      $this->parserClass = '\Drupal\barotraumix\Parser\ParserClassic';
    }
    else {
      throw new Exception("Unable to initialize scanner. Unable to find content package of the app: $this->appId (build id: $this->buildId)");
    }
  }

  /**
   * Prepare URI for application folder.
   *
   * @return string
   */
  protected function prepareUri():string {
    return 'baro://' . $this->appId . '/' . $this->buildId;
  }

  /**
   * Returns name of primary content package of this app.
   *
   * @return string
   */
  protected function primaryContentPackage():string {
    // Use static cache for better performance.
    static $package = NULL;
    if (isset($package)) {
      return $package;
    }
    // Try to detect package name.
    $package = $this->isGame() ? 'Vanilla' : $package;
    $package = $this->isMod() ? 'filelist' : $package;
    return $package;
  }

}
