<?php

/**
 * @file
 * Handler for additional attributes.
 */

namespace Drupal\barotraumix\BaroEntity;

/**
 * Class Attribute.
 */
class Attribute extends BaseEntity {

  /**
   * @var string $name - Name.
   */
  protected string $name;

  /**
   * @var string $value - Value.
   */
  protected string $value;

  /**
   * Class constructor.
   *
   * @param string $name - Attribute name.
   * @param string $value - Attribute value.
   */
  public function __construct(string $name, string $value) {
    // Set entity base info.
    parent::__construct();
    $this->entityBundle = 'attribute';

    // Move data to our object.
    $this->name($name);
    $this->value($value);
  }

  /**
   * Name.
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
   * Value.
   *
   * @param string|NULL $value - Set new value.
   *
   * @return string
   */
  public function value(string $value = NULL):string {
    if (isset($value)) {
      $this->value = $value;
    }
    return $this->value;
  }

  /**
   * @inheritDoc
   */
  protected function entityPrepare():array {
    $entityArray = parent::entityPrepare();
    $entityArray['field_name'] = $this->name();
    $entityArray['field_value'] = $this->value();
    return $entityArray;
  }

}
