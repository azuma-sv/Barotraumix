<?php

/**
 * @file
 * Class to manipulate with Barotraumix entity ContentPackage.
 */

namespace Drupal\barotraumix\BaroEntity;

/**
 * Class ContentPackage.
 */
class ContentPackage extends BaseEntity {

  /**
   * @var string $name - Package name.
   */
  protected string $name;

  /**
   * @var string $gameversion - Version of the game of this package.
   */
  protected string $gameversion;

  /**
   * @var string $modversion - Version of the mod.
   */
  protected string $modversion;

  /**
   * @var string $altnames - Alternative names.
   */
  protected string $altnames;

  /**
   * @var array - Field mapping between BaroEntity and Drupal entity.
   */
  public static array $fieldMapping = [
    'name' => 'field_name',
    'gameversion' => 'field_gameversion',
    'modversion' => 'field_modversion',
    'altnames' => 'field_altnames',
  ];

  /**
   * Class constructor.
   *
   * @param RawNodeData $data
   *   Sanitized object with data from XML.
   */
  public function __construct(RawNodeData $data) {
    // Set entity base info.
    parent::__construct();
    $this->entityBundle = 'package';

    // Move data to our object.
    $this->name = $data->name();
    $attributes = $data->attributes();
    foreach (ContentPackage::$fieldMapping as $property => $field) {
      if (isset($attributes[$property])) {
        $this->$property = $attributes[$property];
        unset($attributes[$property]);
      }
      else {
        $this->$property = '';
      }
    }
    $this->attributes($attributes);

    // Process children.
    $children = [];
    foreach ($data->children() as $child) {
      // @todo: Find out a way to process all child entities.
      if (isset(Asset::$typeMapping[$child->name()])) {
        $children[] = new Asset($child);
      }
    }
    $this->children($children);
  }

  /**
   * Package name.
   *
   * @param string|NULL $value - Set new value.
   *
   * @return string
   */
  public function name(string $value = NULL):string {
    if (isset($value)) {
      $this->name = $value;
    }
    return $this->name;
  }

  /**
   * Package game version.
   *
   * @param string|NULL $value - Set new value.
   *
   * @return string
   */
  public function gameVersion(string $value = NULL):string {
    if (isset($value)) {
      $this->gameversion = $value;
    }
    return $this->gameversion;
  }

  /**
   * Package mod version.
   *
   * @param string|NULL $value - Set new value.
   *
   * @return string
   */
  public function modVersion(string $value = NULL):string {
    if (isset($value)) {
      $this->modversion = $value;
    }
    return $this->modversion;
  }

  /**
   * Package alternative names.
   *
   * @param string|NULL $value - Set new value.
   *
   * @return string
   */
  public function altNames(string $value = NULL):string {
    if (isset($value)) {
      $this->altnames = $value;
    }
    return $this->altnames;
  }

  /**
   * @inheritDoc
   */
  protected function entityPrepare():array {
    $entityArray = parent::entityPrepare();
    foreach (ContentPackage::$fieldMapping as $property => $field) {
      $entityArray[$field] = $this->$property;
    }
    // Process children.
    /** @var \Drupal\barotraumix\BaroEntity\Asset $child */
    foreach ($this->children() as $child) {
      $assetEntity = $child->saveToDrupalEntity();
      $entityArray['field_assets'][] = [
        'target_id'          => $assetEntity->id(),
        'target_revision_id' => $assetEntity->getRevisionId(),
      ];
    }
    return $entityArray;
  }

}
