<?php

/**
 * @file
 *   Functions to actually parse and retrieve shortcode.
 */
require 'vendor/autoload.php';

use Radar\Connect\Connect;
use Radar\Connect\Filter;
use Guzzle\Http\Client;
use Doctrine\Common\Cache\FilesystemCache;
use Guzzle\Cache\DoctrineCacheAdapter;
use Guzzle\Plugin\Cache\CachePlugin;
use Guzzle\Plugin\Cache\DefaultCacheStorage;

/**
 * Radar Connect functions.
 */

function radar_retrieve_events($settings) {
  $client = radar_client();
  // API field names.
  $settings['fields'] = _radar_field_name_mapping($settings['fields']);
  $filter = radar_filter($settings);
  $request = $client->prepareEventsRequest($filter, $settings['fields'], $settings['max_count']);
  return $client->retrieve($request);
}

function radar_retrieve_entities(array $entities) {
  $client = radar_client();
  return $client->retrieveEntityMultiple($entities);
}

function radar_client() {
  static $connect = NULL;

  if (is_null($connect)) {
    $guzzle = new Client();
    $cachePlugin = new CachePlugin(array(
      'storage' => new DefaultCacheStorage(
        new DoctrineCacheAdapter(
          new FilesystemCache(get_temp_dir() . '/wp-radar')
        )
      )
    ));
    // Add the cache plugin to the client object
    $guzzle->addSubscriber($cachePlugin);
    $connect = new Connect($guzzle);
  }
  return $connect;
}

function radar_filter($settings) {
  $filter = new \Radar\Connect\Filter;

  if (!empty($settings['city'])) {
    $filter->addCity($settings['city']);
  }
  if (!empty($settings['group'])) {
    $filter->addGroup($settings['group']);
  }

  return $filter;
}

/**
 * Internal WP plugin functions.
 */

/**
 * Collapses subfields, simplifies field list.
 */
function _radar_field_collect_subfields(&$fields) {
  foreach ($fields as $delta => $listed_field) {
    list($field, $subfield) = explode(':', $listed_field, 2);
    if (!empty($subfield)) {
      // A field with subfields.
      if (!empty($entity_fields[$field])) {
        // Field previously seen, subfields we collect together.
        $entity_fields[$field][] = $subfield;
        unset($fields[$delta]);
      }
      else {
        // Not seen before, add base field name to fields.
        $entity_fields[$field][] = $subfield;
        $fields[$delta] = $field;
      }
    }
  }
  return $entity_fields;
}

/**
 * Mapping for user friendly field names to internal API names.
 */
function _radar_field_name_mapping($fields) {
  // Replace the nice entity names with (exposed) internal field names.
  $nice_names = array('date', 'group', 'location');
  $ugly_names = array('date_time', 'og_group_ref', 'offline');
  return str_replace($nice_names, $ugly_names, $fields);
}

/**
 * Recursively pull the content for fields into an array.
 */
function _radar_parse_items($type, $items, $fields, $subfields = array()) {
  $content = array();
  foreach ((array) $items as $item) {
    $current_content = array();
    // Cycle through the fields in best order that we can.
    foreach ($fields as $field_name) {
      if (!$method = _radar_method_lookup($type, $field_name)) {
        $current_content[] = array('field' => 'error', 'value' => sprintf( __('Unknown field: %s', 'radar_textdomain'), esc_attr($field_name)));
        continue;
      }
      $current_field = $item->$method();
      if (!empty($current_field) && !empty($subfields[$field_name])) {
        $current_field = radar_retrieve_entities($current_field);
        $current_subfields = $subfields[$field_name];
        $current_sub_subfields = _radar_field_collect_subfields($current_subfields);
        $current_content[] = array('entity' => $field_name, 'value' => _radar_parse_items($field_name, $current_field, $current_subfields, $current_sub_subfields));
      }
      else {
        $current_content[] = array('field' => $field_name, 'value' => $current_field);
      }
    }
    $content[] = $current_content;
  }

  return $content;
}

function _radar_method_lookup($type, $field) {
  $lookup = array(
    'event' => array(
      'title' => 'getTitle',
      'date' => 'getDates',
      'body' => 'getBody',
      'url' => 'getUrlView',
      'image' => 'getImageRaw',
      'price' => 'getPrice',
      'price_category' => 'getPriceCategory',
      'email' => 'getEmail',
      'link' => 'getLink',
      'phone' => 'getPhone',
      'group' => 'getGroups',
      'location' => 'getLocations',
      'category' => 'getCategories',
      'topic' => 'getTopics',
      'created' => 'getCreated',
      'updated' => 'getUpdated',
    ),
    'location' => array(
      'title' => 'getTitle',
      'address' => 'getAddress',
      'directions' => 'getDirections',
      'location' => 'getLocation',
    ),
    'topic' => array(
      'title' => 'getTitle',
    ),
    'category' => array(
      'title' => 'getTitle',
    ),
    'price_category' => array(
      'title' => 'getTitle',
    ),
    'group' => array(
      'title' => 'getTitle',
      'body' => 'getBody',
      'category' => 'getCategories',
      'topic' => 'getTopics',
      'location' => 'getLocations',
      'url' => 'getUrlView',
      'group_logo' => 'getGroupLogoRaw',
      'email' => 'getEmail',
      'link' => 'getLink',
      'phone' => 'getPhone',
      'opening_times' => 'getOpeningTimes',
    )
  );
  return !empty($lookup[$type][$field]) ? $lookup[$type][$field] : FALSE;
}

