<?php

namespace Radar\Connect\Entity;

class Node extends Entity {
  public $title;
  public $body;
  public $category;
  public $topic;
  public $url;
  public $edit_url;
  public $status;
  public $created;
  public $changed;
  public $language;

  public function __construct($data = array()) {
    $this->set($data);
  }

  public function set($data) {
    parent::set($data);
    if (isset($data['nid'])) {
      $this->drupalId = $data['nid'];
    }
    if (isset($data['vid'])) {
      $this->drupalVersionId = $data['vid'];
    }
  }

  public function apiUri() {
    if (isset($this->apiUri)) {
      return $this->apiUri;
    }
    elseif (isset($this->uuid)) {
      return $this->apiBase . 'node/' . $this->uuid;
    }

    throw new Exception();
  }

  public function getTitle() {
    return $this->title;
  }

  /**
   * Body or Description of the Entity.
   *
   * @return string
   */
  public function getBody() {
    return (!empty($this->body['value'])) ? $this->body['value'] : '';
  }

  /**
   * Body, summary and filter type.
   *
   * Keyed array with 'value', 'summary' if there is a shorter summary
   * and 'format' the Radar internal name of the filter format used.
   *
   * @return array
   */
  public function getBodyRaw() {
    return $this->body;
  }

  public function getCategoriesRaw() {
    return $this->category;
  }

  /**
   * Return standard categories.
   *
   * @return TaxonomyTerm[]
   */
  public function getCategories() {
    $categories = array();
    if (is_array($this->category)) {
      foreach ($this->category as $category) {
        $categories[] = new TaxonomyTerm($category);
      }
    }
    return $categories;
  }

  public function getTopicsRaw() {
    return $this->topics;
  }

  /**
   * Return free tagging topics
   *
   * @return TaxonomyTerm[]
   */
  public function getTopics() {
    $topics = array();
    if (is_array($this->topic)) {
      foreach ($this->topic as $topic) {
        $topics[] = new TaxonomyTerm($topic);
      }
    }
    return $topics;
  }

  /**
   * URL for the event on the site.
   *
   * @return string
   */
  public function getUrlView() {
    return $this->url;
  }

  /**
   * URL to edit the event on the site.
   *
   * @return string
   */
  public function getUrlEdit() {
    return $this->edit_url;
  }

  /**
   * Published status.
   *
   * @return bool
   *   TRUE if published.
   */
  public function getStatus() {
    return (bool) $this->status;
  }

  /**
   * Created time.
   *
   * @return \DateTime
   */
  public function getCreated() {
    $created = new \DateTime();
    $created->setTimestamp($this->created);
    return $created;
  }

  /**
   * Last updated time.
   *
   * @return \DateTime
   */
  public function getUpdated() {
    $updated = new \DateTime();
    $updated->setTimestamp($this->changed);
    return $updated;
  }

  /**
   * Language code for entity version.
   *
   * The entity may be available in other languages. This is the language
   * code for the present version.
   *
   * @return string
   */
  public function getLanguage() {
    return $this->language;
  }
}
