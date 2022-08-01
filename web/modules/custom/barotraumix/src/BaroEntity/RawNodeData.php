<?php
/**
 * @file
 * Raw, but parsed data from XML node.
 */

namespace Drupal\barotraumix\BaroEntity;

use Drupal\Component\Utility\Html;
use Exception;

/**
 * Class RawNodeData.
 */
class RawNodeData {

  /**
   * @var string $name - Name of the node.
   */
  protected string $name;

  /**
   * @var array $attributes - Array of attributes.
   */
  protected array $attributes;

  /**
   * @var array $children - Child nodes.
   */
  protected array $children;

  /**
   * @var array $tagMapping - Array which contains mapping which will help us to use case-insensitive code.
   */
  public static array $tagMapping = [
    'contentpackage' => 'ContentPackage',
    'item' => 'Item',
  ];

  /**
   * Object constructor.
   *
   * @todo: Disable validation. Make filtration.
   *
   * @param string $name
   *  Name of the node.
   * @param array $attributes
   *  Array of attributes.
   * @param array $children
   *  Child nodes.
   */
  public function __construct(string $name, array $attributes = [], array $children = []) {
    // Process node name.
    $name = mb_strtolower(Html::escape($name));
    if (empty($name)) {
      throw new Exception('XML node can\'t have empty name');
    }
    if (isset($this::$tagMapping[$name])) {
      $name = $this::$tagMapping[$name];
    }
    $this->name = $name;
    // Process node attributes.
    array_walk($attributes, function ($value, $key) {
      if (!is_scalar($value)) {
        throw new Exception('Attribute values should contain only scalar values.');
      }
    });
    $this->attributes = $attributes;
    // Process node children.
    array_walk($children, function ($value, $key) {
      if (!is_object($value)) {
        throw new Exception('Children of RawNodeData object should be an object of the same class');
      }
      if (!$value instanceof RawNodeData) {
        throw new Exception('Children of RawNodeData object can\'t contain objects of another class.');
      }
    });
    $this->children = $children;
  }

  /**
   * Name of the node.
   *
   * @return string
   */
  public function name():string {
    return $this->name;
  }

  /**
   * Node attributes.
   *
   * @return array
   */
  public function attributes():array {
    return $this->attributes;
  }

  /**
   * Child nodes.
   *
   * @return array
   */
  public function children():array {
    return $this->children;
  }

}
