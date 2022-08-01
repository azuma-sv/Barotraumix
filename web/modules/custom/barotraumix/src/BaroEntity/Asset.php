<?php

/**
 * @file
 * Class to manipulate with Barotraumix asset entity.
 */

namespace Drupal\barotraumix\BaroEntity;

/**
 * Class Asset.
 */
class Asset extends BaseEntity {

  /**
   * @var string $type - Asset type.
   */
  protected string $type;

  /**
   * @var string $file - File path.
   */
  protected string $file;

  /**
   * @var array $typeMapping - Field mapping between BaroEntity and Drupal entity.
   */
  public static array $typeMapping = [
    'item' => 2,
    'text' => 1,
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
    $this->entityBundle = 'assets';

    // Move data to our object.
    $this->type($data->name());
    $attributes = $data->attributes();
    if (!empty($attributes['file'])) {
      $this->file($attributes['file']);
    }
    unset($attributes['file']);
    $this->attributes($attributes);
  }

  /**
   * Asset type.
   *
   * @param string|NULL $value - Set new value.
   *
   * @return string
   */
  public function type(string $value = NULL):string {
    if (isset($value)) {
      $this->type = $value;
    }
    return $this->type;
  }

  /**
   * Asset file.
   *
   * @param string|NULL $value - Set new value.
   *
   * @return string
   */
  public function file(string $value = NULL):string {
    if (isset($value)) {
      $this->file = $value;
    }
    return $this->file;
  }

  /**
   * @inheritDoc
   */
  protected function entityPrepare():array {
    $entityArray = parent::entityPrepare();
    // Prepare asset type.
    if (isset(Asset::$typeMapping[$this->type()])) {
      $target = Asset::$typeMapping[$this->type()];
      $entityArray['field_asset_type'] = [
        'target_id'          => $target,
        'target_revision_id' => $target,
      ];
    }
    else {
      // todo: What to do with unsupported assets?
      unset($target);
    }
    // Prepare file.
    // @todo: Prepare file.
    return $entityArray;
  }

}
