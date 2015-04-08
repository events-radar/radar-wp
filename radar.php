<?php

/* ==================================================================
 * Plugin Name: Radar integration
 * Description: Provides widget and short code to display radar events.
 * Plugin URI: http://radar.squat.net/
 * Author: Ekes
 * Version: 0.1.0-alpha1
 * ================================================================== */

// TODO cron updating; and caching.
// TODO language setting for content from API.

//
// SHORTCODE
//

//
// [radar_events]
//

/**
 * Generate event list shortcode content.
 */
function radar_shortcode_events($attributes, $content = '') {
  require_once('radar.inc.php');

  $defaults = array(
    'api_url' => 'https://new-radar.iskra.net/',
    'max_count' => 5,
    'city' => '',
    'group' => '',
    'fields' => array(
      'title',
      'date',
      'location:address',
      'location:directions',
      'category:title',
      'topic:title',
      'price_category:title',
      'price',
      'link',
      'body',
      'group:title',
      'group:url',
      'created',
      'url',
    ),
  );
  $event_settings = shortcode_atts($defaults, $attributes, 'radar_events');
  // Collect subfields of entities together, and get a list.
  // Edits the actual field list to collect the entity fields together.
  $fields = $event_settings['fields'];
  $subfields = _radar_field_collect_subfields($fields);
  $event_settings['fields'] = $fields;
  $events = radar_retrieve_events($event_settings);
  // Now parse these objects and retrieve any subfields into arrays.
  $events = _radar_parse_items('event', $events, $fields, $subfields);
  $content = '';
  foreach ($events as $event) {
    $content .= radar_format_item('event', $event, array('type' => 'radar_events', 'attributes' => $attributes));
  }
  return $content;
}
add_shortcode('radar_events', 'radar_shortcode_events');

//
// TODO [radar_groups]
//

//
//
// FILTERS
//
// To alter the values and HTML of the output for a shortcode.
//
//

//
// 'radar_shortcode_field_value'
//

/**
 * Basic implementation of 'radar_shortcode_field_value' filter.
 *
 * Prepare the value of a field to be put into HTML.
 * Doesn't actually do anything but set the 'value' to become the 'output used;
 * and the 'field' name to become the 'type' used for HTML formatting filter.
 *
 * @param array $field
 *   Keyed array of data: ['value'] parsed value; ['field'] field name.
 * @param string type
 *   Context: The item type this field is attached.
 * @param array $item
 *   Context: The full item itself. Useful for merging field content together.
 * @param array $parents
 *   Context: Any parent items. So the topic of an event has the event item in
 *   this array. It is empty if it is a field on the event itself.
 * @param array $shortcode
 *   Context: Array with short code type eg. radar_events and the attributes.
 *
 * @return array
 *   Altered $field array. Finally requires keys ['output'] for output value
 *   to be used, and ['type'] for the type to be used.
 */
function radar_shortcode_field_value($field, $type, $item, $parents, $shortcode) {
  $field['output'] = $field['value'];
  $field['type'] = $field['field'];
  return $field;
}
add_filter('radar_shortcode_field_value', 'radar_shortcode_field_value', 10, 5);

/**
 * Date field formatting implementation of 'radar_shortcode_field_value' filter.
 *
 * The dates are in http://php.net/DateTime format. The 'date' field has ['start'],
 * ['end'] times, it also has a string ['rrule'] iCal repeat rule.
 */
function radar_shortcode_field_date_value($field, $type, $item, $parents, $shortcode) {
  // Fields that need additional formatting.
  switch ($field['field']) {
    case 'created':
    case 'updated':
      if ($field['value']->getTimestamp() != 0) {
        $field['output'] = $field['field'] == 'created' ? __('Created: ', 'radar_textdomain') : __('Updated: ', 'radar_textdomain');
        $field['output'] .= $field['value']->format('Y-m-d H:i:s');
      }
      else {
        $field['output'] = '';
        $field['type'] = '_none';
      }
      break;
    case 'date':
      $field['output'] = array();
      foreach ($field['value'] as $date) {
        $this_date = sprintf(__('Start: %s', 'radar_textdomain'), $date['start']->format('Y-m-d H:i:s'));
        if ($field['value']['start'] != $field['value']['end']) {
          $this_date .= '<br />'
            . sprintf(__('End: %s', 'radar_textdomain'), $date['end']->format('Y-m-d H:i:s'));
        }
        $field['output'][] = $this_date;
      }
      break;
  }

  return $field;
}
add_filter('radar_shortcode_field_value', 'radar_shortcode_field_date_value', 12, 5);

/**
 * Location field implementation of 'radar_shortcode_field_value' filter.
 *
 * The location field location:location is a https://github.com/phayes/geoPHP object.
 * It is usually a point.
 */
function radar_shortcode_field_location_value($field, $type, $item, $parents, $shortcode) {
  if ($field['field'] == 'location') {
    $field['output'] = $field['value']->out('wkt');
  }

  return $field;
}
add_filter('radar_shortcode_field_value', 'radar_shortcode_field_location_value', 12, 5);

/**
 * Item Radar URL implementation of 'radar_shortcode_field_value' filter.
 *
 * If there is a title, and the link to the item on radar, this is turned into a link.
 * If there is a url field, but this is a sub-item, it has parents (group of an
 * event for example), then the url is not shown. If it is on the main item then it's
 * turned into a 'more…' link.
 */
function radar_shortcode_field_url_value($field, $type, $item, $parents, $shortcode) {
  if ($field['field'] == 'title') {
    foreach ($item as $other_field) {
      if ($other_field['field'] == 'url') {
        $field['output'] = '<a href="'. $other_field['value'] .'">' . $field['output'] . '</a>';
      }
    }
  }
  if ($field['field'] == 'url') {
    if (count($parents)) {
      $field['output'] = '';
      $field['type'] = '_none';
    }
    else {
      $field['output'] = '<a href="' . $field['value'] . '">' . __('more…', 'radar_textdomain') . '</a>';
    }
  }

  return $field;
}
add_filter('radar_shortcode_field_value', 'radar_shortcode_field_url_value', 12, 5);

//
// 'radar_shortcode_field_html'
//

/**
 * Basic implementation of 'radar_shortcode_field_html' filter.
 *
 * Put the output into HTML.
 * Before this implementation is run output for multiple fields are in an array.
 *
 * @param array|string $output
 *   The output to be put into HTML to be displayed.
 * @param array $field
 *   Context: Keyed array of data: ['value'] parsed value; ['field'] field name,
 *   ['output'] the original $output before changes ['type'] the requested type.
 * @param string type
 *   Context: The item type this field is attached.
 * @param array $item
 *   Context: The full item itself. Useful for merging field content together.
 * @param array $parents
 *   Context: Any parent items. So the topic of an event has the event item in
 *   this array. It is empty if it is a field on the event itself.
 * @param array $shortcode
 *   Context: Array with short code type eg. radar_events and the attributes.
 *
 * @return array
 *   Altered $field array. Finally requires keys ['output'] for output value
 *   to be used, and ['type'] for the type to be used.
 */
function radar_shortcode_field_html($output, $field, $type, $item, $parents, $shortcode) {
  if (is_array($output)) {
    $output = implode(', ', $output);
  }

  return $output;
}
add_filter('radar_shortcode_field_html', 'radar_shortcode_field_html', 10, 6);

/**
 * Link field HTML implementation of 'radar_shortcode_field_html' filter.
 *
 * Turns the external link field into a clickable URL.
 *
 * The field itself is an array, so this is run before the array is collapsed
 * by radar_shortcode_field_html (weight 8 before 10).
 */
function radar_shortcode_field_link_html($output, $field, $type, $item, $parents, $shortcode) {
  if ($field['type'] == 'link') {
    foreach ($output as &$link) {
      $link = '<a href="' . $link . '">' . $link . '</a>';
    }
  }

  return $output;
}
add_filter('radar_shortcode_field_html', 'radar_shortcode_field_link_html', 8, 6);

/**
 * Title field HTML implentation of 'radar_shortcode_field_html' filter.
 */
function radar_shortcode_field_title_html($output, $field, $type, $item, $parents, $shortcode) {
  if ($field['type'] == 'title' && !count($parents)) {
    $output = '<h3>' . $output . '</h3>';
  }

  return $output;
}
add_filter('radar_shortcode_field_html', 'radar_shortcode_field_title_html', 12, 6);

/**
 * Subfields HTML implementation of 'radar_shortcode_field_html' filter.
 *
 * All the fields together of an event location/topic/...
 */
function radar_shortcode_field_subfields_html($output, $field, $type, $item, $parents, $shortcode) {
  if (($type == 'event' || $type == 'group') && !empty($field['entity'])) {
    $output = '<div class="' . $field['entity'] . '">' . $output . '</div>';
  }

  return $output;
}
add_filter('radar_shortcode_field_html', 'radar_shortcode_field_subfields_html', 12, 6);

/**
 * Default paragraph HTML for fields, implementation of 'radar_shortcode_field_html' filter.
 *
 * For fields or the main item, not subfields.
 */
function radar_shortcode_field_paragraph_html($output, $field, $type, $item, $parents, $shortcode) {
  if (empty($parents) && in_array($field['type'], array('date', 'url', 'price', 'email', 'link', 'phone', 'created', 'updated', 'opening_times'))) {
    $output = '<p class="' . $field['type'] . '">' . $output . '</p>';
  }

  return $output;
}
add_filter('radar_shortcode_field_html', 'radar_shortcode_field_paragraph_html', 12, 6);


//
// Other WP hooks.
//

/**
 * Registers translation text domain.
 */
function radar_textdomain() {
  if (function_exists('load_plugin_textdomain')) {
    load_plugin_textdomain('radar_textdomain', FALSE, dirname(plugin_basename(__FILE__)) . '/languages');
  }
}
add_action('init', 'radar_textdomain');

/**
 * Shortcode filter callback.
 *
 * Enable an array of fields from the comma separated list on fields attribute.
 */
function radar_shortcode_parse($out, $pairs, $atts) {
  if (!empty($atts['fields'])) {
    $out['fields'] = explode(',', $atts['fields']);
  }

  return $out;
}
add_filter('shortcode_atts_radar_events', 'radar_shortcode_parse', 10, 3);

//
// Helper functions
//

/**
 * Format item's parsed fields array for radar shortcode's.
 */
function radar_format_item($type, $item, $shortcode, $parents = array()) {
  $content = '';

  foreach ($item as $field) {
    // Item containing other items.
    if (!empty($field['entity'])) {
      $entity_content = array();
      foreach ($field['value'] as $entity) {
        $entity_content[] = radar_format_item($field['entity'], $entity, $shortcode, array_merge($parents, array($field)));
      }
      $content .= apply_filters('radar_shortcode_field_html', $entity_content, $field, $type, $item, $parents, $shortcode);
    }
    else {
      $field = apply_filters('radar_shortcode_field_value', $field, $type, $item, $parents, $shortcode);
      $content .= apply_filters('radar_shortcode_field_html', $field['output'], $field, $type, $item, $parents, $shortcode);
    }
  }
  return $content;
}
