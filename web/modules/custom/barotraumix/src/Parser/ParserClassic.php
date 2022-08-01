<?php

/**
 * @file
 * Main parsing functionality.
 *
 * It's called ParserClassic because some day Barotrauma may make significant changes into their structure.
 * I would like to make architecture when we will have backward compatibility with code which is active right now (Summer 2022).
 */

namespace Drupal\barotraumix\Parser;

use Exception;
use SimpleXMLElement;
use Drupal\Component\Utility\Html;
use Drupal\Core\File\FileSystemInterface;
use Drupal\barotraumix\BaroEntity\RawNodeData;

/**
 * Class ParserClassic.
 */
class ParserClassic implements ParserInterface {

  /**
   * @var string $name - Name of the main node.
   */
  protected string $name;

  /**
   * @var array $attributes - Array of attributes of this node.
   */
  protected array $attributes;

  /**
   * @var SimpleXMLElement $xmlParser - XML Parser object.
   */
  protected SimpleXMLElement $xmlParser;

  /**
   * @var FileSystemInterface $fileSystem - File system service.
   */
  protected FileSystemInterface $fileSystem;

  /**
   * @var RawNodeData $data - Storage for parsed data.
   */
  protected RawNodeData $data;

  /**
   * @var array $children - Child elements.
   */
  protected array $children;

  /**
   * @var string $uri - URI to the file which we are trying to parse.
   */
  protected string $uri;

  /**
   * Class constructor.
   *
   * @param string $uri
   *  URI to the XML file to parse.
   * @param FileSystemInterface $fileSystem
   *  File system service.
   * @param string|NULL $name
   *  Name of the main node of the parser.
   */
  public function __construct(string $uri, FileSystemInterface $fileSystem, string $name = NULL) {
    // Prepare parser.
    $realPath = $fileSystem->realpath($uri);
    $content = file_get_contents($realPath);
    if ($content === FALSE) {
      throw new Exception("Unable to read content of the file: $uri");
    }
    $this->uri = $uri;
    $this->fileSystem = $fileSystem;
    $this->xmlParser = new SimpleXMLElement($content);

    // Check if this is a content package.
    if (!isset($name)) {
      $name = mb_strtolower(Html::escape($this->xmlParser->getName()));
    }
    if (isset(RawNodeData::$tagMapping[$name])) {
      $name = RawNodeData::$tagMapping[$name];
    }
    if ($name == 'ContentPackage') {
      $this->setName($name);
    }
  }

  /**
   * Name of the main node.
   *
   * @return string
   */
  public function name():string {
    if (!isset($this->name)) {
      $msg = "Requested name of the node which has no name. Probably this file is corrupted: $this->uri";
      throw new Exception($msg);
    }
    return $this->name;
  }

  /**
   * Attributes of the main node of this file.
   *
   * @return array
   *  Array of attributes.
   */
  public function attributes():array {
    // Check if this parser already has a name.
    if (!isset($this->name)) {
      $msg = "Requested attributes of the node which has no name. Probably this file is corrupted: $this->uri";
      throw new Exception($msg);
    }
    return $this->attributes;
  }

  /**
   * Private method to set name of the main node.
   *
   * @param string $name
   *  Name of the main node of this file.
   *
   * @return void
   */
  public function setName(string $name):void {
    // We can't change existing name.
    if (isset($this->name)) {
      $msg = "Can't change name of the parser which already has a name (from '$this->name' to '$name'].";
      throw new Exception($msg);
    }
    if (isset(RawNodeData::$tagMapping[$name])) {
      $name = RawNodeData::$tagMapping[$name];
    }
    // Set new name.
    $this->name = $name;
    // Set attributes.
    $this->xmlParser->rewind();
    $attributes = (array) $this->xmlParser->attributes();
    $this->attributes = !empty($attributes['@attributes']) ? $attributes['@attributes'] : [];
  }

  /**
   * Get all data from this parser.
   *
   * @return RawNodeData
   */
  public function data():RawNodeData {
    // Return cached.
    if (isset($this->data)) {
      return $this->data;
    }
    // Prepare data.
    if ($this->name() == 'ContentPackage') {
      $children = $this->parseContentPackage();
    }
    // Store to cache.
    $this->data = new RawNodeData($this->name(), $this->attributes(), $this->children);
    return $this->data;
  }

  /**
   * Method to parse content package data.
   *
   * @return array
   */
  protected function parseContentPackage():array {
    // Validate data.
    if (isset($this->children)) {
      return $this->children;
    }

    // Prepare data.
    $this->children = [];
    $xml = (array) $this->xmlParser;
    unset($xml['@attributes']);
    $keys = array_keys($xml);

    // Walk through all elements.
    foreach ($keys as $name) {
      foreach ($this->xmlParser->$name as $key => $simpleXML) {
        $data = (array) $simpleXML;
        $attributes = !empty($data['@attributes']) ? $data['@attributes'] : [];
        $this->children[] = new RawNodeData($name, $attributes);
      }
    }

    return $this->children;
  }

}
