<?php

namespace Radar\Connect\Entity;

use geoPHP;

class Location extends Entity {
  public $title;
  public $address;
  public $directions;
  public $map;
  public $timezone;

  public function __construct($data = array()) {
    $this->set($data);
    $this->type = 'location';
  }

  public function set($data) {
    $data = (array) $data;
    parent::set($data);
    if (isset($data['id'])) {
      $this->drupalId = $data['id'];
    }
  }

  public function apiUri() {
    if (isset($this->apiUri)) {
      return $this->apiUri;
    }
    elseif (isset($this->uuid)) {
      return $this->apiBase . 'location/' . $this->uuid;
    }

    throw new Exception();
  }

  public function getTitle() {
    return $this->title;
  }

  /**
   * Return address array.
   *
   * array
   *  ['country']                   two letter code
   *  ['name_line']                 locally known as
   *  ['first_name']
   *  ['last_name']
   *  ['organisation_name']
   *  ['administrative_area']
   *  ['sub_administrative_area']
   *  ['locality']                  city name
   *  ['dependent_locality']
   *  ['postal_code']               postcode
   *  ['thoroughfare']              street
   *  ['premise']
   *
   * @return array
   */
  public function getAddressRaw() {
    return $this->address;
  }

  /**
   * Return components of the address, glued with string.
   *
   * @param array $include
   *   Key names. See self::getAddressRaw() for list.
   * @param string $seperator
   *   Glue to join components.
   *
   * @return string
   */
  public function getAddress($include = array('name_line', 'thoroughfare', 'locality'), $seperator = ', ') {
    $address = array();
    foreach ($include as $part) {
      if (! empty($this->address[$part])) {
        $address[] = $this->address[$part];
      }
    }
    return implode($seperator, $address);
  }

  public function getDirectionsRaw() {
    return $this->directions;
  }

  /**
   * Return the directions text field.
   *
   * Free text field used for describing how to get to a location.
   *
   * @return string
   */
  public function getDirections() {
    return $this->directions;
  }

  public function getLocationRaw() {
    return $this->map;
  }

  /**
   * Return the geographic location.
   *
   * Usually a point, geocoded or manually added.
   *
   * @return geoPHP\Geometry
   */
  public function getLocation() {
    return geoPHP::load($this->map['geom']);
  }
}
