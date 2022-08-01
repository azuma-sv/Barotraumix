<?php

/**
 * @file
 * Abstract class which needs to be inherited by other BaroEntities.
 */

namespace Drupal\barotraumix\BaroEntity;

use Drupal\Core\Entity\EntityInterface;
use Exception;

/**
 * Abstract class BaseEntity.
 */
abstract class BaseEntity {

  /**
   * @var EntityInterface $entity - Entity object if exists.
   */
  protected EntityInterface $entity;

  /**
   * @var string $drupalEntityClass - Drupal entity class name to store BaroEntity.
   */
  protected string $drupalEntityClass;

  /**
   * @var string $entityType - Drupal entity type to store BaroEntity.
   */
  protected string $entityType;

  /**
   * @var string $entityBundle - Drupal entity bundle to store BaroEntity.
   */
  protected string $entityBundle;

  /**
   * @var array $attributes - Additional attributes.
   */
  protected array $attributes = [];

  /**
   * @var array $children - Child entities.
   */
  protected array $children = [];

  /**
   * Class constructor.
   */
  public function __construct() {
    // By default, we use paragraphs, but some entities might use nodes.
    $this->drupalEntityClass = 'Drupal\paragraphs\Entity\Paragraph';
    $this->entityType = 'paragraph';
    // Need to override in child class.
    $this->entityBundle = '';
    // @todo: Implement Drupal entity for unknown BaroEntities.
  }

  /**
   * Additional attributes.
   *
   * @param array|NULL $attributes - Array of additional attributes.
   *
   * @return array
   */
  public function attributes(array $attributes = NULL):array {
    if (isset($attributes)) {
      $this->attributes = $attributes;
    }
    return $this->attributes;
  }

  /**
   * Children.
   *
   * @param array|NULL $children - Array of additional attributes.
   *
   * @return array
   */
  public function children(array $children = NULL):array {
    if (isset($children)) {
      $this->children = $children;
    }
    return $this->children;
  }

  /**
   * Saves BaroEntity to database as Drupal entity.
   *
   * @return EntityInterface
   */
  public function saveToDrupalEntity():EntityInterface {
    // Discard new changes.
    if (isset($this->entity)) {
      throw new Exception('This BaroEntity has been saved already.');
    }
    /** @var $drupalEntityClass EntityInterface */
    $drupalEntityClass = $this->drupalEntityClass;
    $entity = $drupalEntityClass::create($this->entityPrepare());
    $entity->save();
    $this->entity = $entity;
    return $entity;
  }

  /**
   * Returns machine name of current entity.
   *
   * @todo Maybe I should remove this method?
   *
   * @return string
   */
  protected function entityType():string {
    return $this->entityType;
  }

  /**
   * Prepares entity structure before it's saved to database.
   *
   * Useful to override in child classes.
   *
   * @return array
   */
  protected function entityPrepare():array {
    // Prepare array.
    $entity = [];
    $entity['type'] = $this->entityBundle;
    // Inject all attributes.
    foreach ($this->attributes() as $key => $value) {
      if (!empty($value)) {
        $attribute = new Attribute($key, $value);
        $attributeEntity = $attribute->saveToDrupalEntity();
        $entity['field_attributes'][] = [
          'target_id'          => $attributeEntity->id(),
          'target_revision_id' => $attributeEntity->getRevisionId(),
        ];
      }
    }
    return $entity;
  }

}
