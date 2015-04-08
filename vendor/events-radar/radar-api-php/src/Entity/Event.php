<?php

namespace Radar\Connect\Entity;

class Event extends Node {
  public $og_group_ref;
  public $date_time;
  public $image;
  public $price_category;
  public $price;
  public $email;
  public $link;
  public $offline;
  public $phone;

  public function __construct($data = array()) {
    parent::__construct($data);
    $this->type = 'event';
  }

  public function set($data) {
    parent::set($data);
    if (isset($data['title_field'])) {
      // @todo always title_field?
      $this->title = $data['title_field'];
    }
  }

  public function getGroupsRaw() {
    return $og_group_ref;
  }

  /**
   * Return associated groups as group entities.
   *
   * @return Group[]
   */
  public function getGroups() {
    $groups = array();
    foreach ($this->og_group_ref as $group) {
      $groups[] = new Group($group);
    }
    return $groups;
  }

  /**
   * Return raw event date array.
   *
   * An array of keyed arrays.
   *
   * Array[]
   *  ['value']            start unix timestamp
   *  ['value2']           end unix timestamp
   *  ['time_start']       start ISO 8601 time with timezone
   *  ['time_end']         end ISO 8601 time with timezone
   *  ['rrule']            RFC5545 iCalendar repeat rule
   *
   * @return array
   */
  public function getDatesRaw() {
    return $this->date_time;
  }

  /**
   * Return event date.
   *
   * An array of keyed arrays.
   *
   * Array[]
   *  ['start']           \DateTime start
   *  ['end']             \DateTime|null end
   *  ['rrule']           RFC 5545 iCalendar repeat rule
   *
   * @return array
   */
  public function getDates() {
    $dates = array();
    foreach ($this->date_time as $feed_date) {
      $this_date = array();
      $this_date['start'] = new \DateTime($feed_date['time_start']);
      $this_date['end'] = empty($feed_date['time_end']) ? null : new \DateTime($feed_date['time_end']);
      $this_date['rrule'] = $feed_date['rrule']; // Null if not set.
      $dates[] = $this_date;
    }
    return $dates;
  }

  /**
   * Return image field data.
   *
   * TODO API isn't putting the data into the output.
   */
  public function getImageRaw() {
    return $this->image;
  }

  public function getPriceCategoryRaw() {
    return $this->price_category;
  }

  /**
   * Return price category.
   *
   * @return TaxonomyTerm[]
   */
  public function getPriceCategory() {
    $price_categories = array();
    if (is_array($this->price_category)) {
      foreach ($this->price_category as $price_category) {
        $price_categories[] = new TaxonomyTerm($price_category);
      }
    }
    return $price_categories;
  }


  /**
   * Return the price, free text field.
   *
   * @return string
   *   Array of strings describing price.
   */
  public function getPrice() {
    return $this->price;
  }

  public function getPriceRaw() {
    return $this->price;
  }

  /**
   * Return email.
   *
   * @return string
   */
  public function getEmail() {
    return $this->email;
  }

  public function getEmailRaw() {
    return $this->email;
  }

  /**
   * Return array of url links for the event.
   *
   * @return string[]
   */
  public function getLink() {
    $links = array();
    foreach ($this->link as $link) {
      $links[] = $link['url'];
    }
    return $links;
  }

  /**
   * Return array of array url links for the event.
   *
   * Keyed with 'url', and unused 'attributes'.
   *
   * @return array
   */
  public function getLinkRaw() {
    return $this->link;
  }

  public function getLocationsRaw() {
    return $this->offline;
  }

  /**
   * Return event locations.
   *
   * @return Location[]
   */
  public function getLocations() {
    $locations = array();
    foreach ($this->offline as $location) {
      $locations[] = new Location($location);
    }
    return $locations;
  }

  /**
   * Return phone number.
   *
   * @return string
   */
  public function getPhone() {
    return $this->phone;
  }

  public function getPhoneRaw() {
    return $this->phone;
  }

}
