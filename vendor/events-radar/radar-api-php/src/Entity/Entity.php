<?php

namespace Radar\Connect\Entity;

abstract class Entity {

  public $drupalId;
  public $drupalVersionId;
  public $uuid;
  public $vuuid;
  public $type;
  protected $apiUri;
  protected $apiBase;

  /**
   * TODO move this to the controller Connect class.
   */
  static function className($type) {
    $classes = array(
      'node' => 'Node',
      'group' => 'Group',
      'event' => 'Event',
      'category' => 'Category',
      'listings_group' => 'ListingsGroup',
      'location' => 'Location',
      'taxonomy_term' => 'TaxonomyTerm',
      'category' => 'TaxonomyTerm',
      'topic' => 'TaxonomyTerm',
      'price' => 'TaxonomyTerm',
    );
    return $classes[$type];
  }

  abstract public function __construct($data = array());

  /**
   * Set data for entity.
   *
   * @param array $data
   */
  public function set($data) {
    foreach ($this as $key => $value) {
      if (isset($data[$key])) {
        $this->$key = $data[$key];
      }
    }
    if (isset($data['uri'])) {
      $this->apiUri = $data['uri'];
    }
  }

  /**
   * Return the API URI for this entity.
   *
   * @return string
   */
  abstract function apiUri();

  /**
   * Return the UUID for the entity.
   *
   * @return string|null
   */
  public function getUuid() {
    return !empty($this->uuid) ? $this->uuid : null;
  }

  /**
   * Return the Version UUID for the entity.
   *
   * @return string|null
   */
  public function getVuuid() {
    return !empty($this->vuuid) ? $this->vuuid : null;
  }

  /**
   * Return the Drupal internal ID for the entity.
   *
   * @return int|null
   */
  public function getInternalId() {
    return !empty($this->drupalId) ? $this->drupalId : null;
  }

  /**
   * Return the Drupal internal version ID for the entity.
   *
   * @return int|null
   */
  public function getInternalVid() {
    return !empty($this->drupalVersionId) ? $this->drupalVersionId : null;
  }
}
