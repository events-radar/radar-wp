<?php

/**
 * @file
 *   Example creating HTML of retrieved radar events based on a filter.
 */

/**
 * Configuration options.
 */
define('CACHE_PATH', '/tmp/radar-cache');


// Load radar client code and get an instance.
require('radar_client.php');

// Shared radar connect client.
$client = radar_client();
// Basic cache for output.
$cache = radar_cache();

// Check to see if there is a copy in the cache.
if ($cache->contains('events.php') && $page = $cache->fetch('events.php')) {
  // We can handle expiring data, and serve a stale page.
  print $page['html'];
  // If it's more than an hour old, get a new one.
  if ($page['created'] + 60 * 60 < time()) {
    $events = radar_events_retrieve($client);
    $html = radar_events_format($client, $events);
    $cache->delete('events.php');
  }
}
else {
  // Generate the page and output it.
  $events = radar_events_retrieve($client);
  $html = radar_events_format($client, $events, true);
}

if (!empty($html)) {
  // Save generated HTML into the cache.
  $page = array('html' => $html, 'created' => time());
  $cache->save('events.php', $page);
}

/**
 * Set a filter and retrieve events matching the filter.
 *
 * @param \Radar\Connect\Connect $client
 *   The connect client.
 *
 * @return Radar\Connect\Event[]
 *   Array of radar connect events.
 */
function radar_events_retrieve(\Radar\Connect\Connect $client) {
  $filter = new \Radar\Connect\Filter;
  $filter->addCity('Berlin');
  // Alternatives:-
  //$filter->addCity('Amsterdam');
  //$filter->addDate(new DateTime('tomorrow'));
  //$filter->addDay();
  // See docs/classes/Radar.Connect.Filter.html for full list of methods.

  // Get the request.
  // arguments:
  //   $filter - prepared above,
  //   $fields - array of field names to collect, empty for default
  //   $limit - maximum number of events to return.
  $request = $client->prepareEventsRequest($filter, array(), 50);
  // Execute request.
  return $client->retrieve($request);
}

/**
 * Create HTML of an array of events.
 *
 * @param \Radar\Connect\Connect $client
 *   The connect client.
 * @param \Radar\Connect\Event[] $events
 *   Array of Event entities, for example response to events request.
 * @param bool $output
 *   If HTML output should also be sent to stdout.
 *
 * @return string
 *   The HTML output.
 */
function radar_events_format(\Radar\Connect\Connect $client, array $events, $output = FALSE) {
  ob_start();
  ob_implicit_flush(TRUE);
  $html = '';

  foreach ($events as $event) {
    // Title and date.
    print '<h1>' . $event->getTitle() . '</h1>';
    print $event->getBody();
    $dates = $event->getDates();
    $date = current($dates);
    print $date['start']->format('Y-m-d H:i:s');

    // The groups are references. If we want to get details about
    // them we actually load the group itself as well.
    $groups = $client->retrieveEntityMultiple($event->getGroups());
    foreach ($groups as $group) {
      print '<p><strong>' . $group->getTitle() . '</strong></p>';
    }

    // Just as with the groups the locations are just the references.
    // So we load them here.
    $locations = $client->retrieveEntityMultiple($event->getLocations());
    foreach ($locations as $location) {
      print '<p>' . $location->getAddress() . '</p>';
    }

    // Yep and the categories, and topics.
    $categories = $client->retrieveEntityMultiple($event->getCategories());
    $category_names = array();
    foreach ($categories as $category) {
      $category_names[] = $category->getTitle();
    }
    if (! empty($category_names)) {
      print '<p>Categories: ' . implode(', ', $category_names);
    }

    $topics = $client->retrieveEntityMultiple($event->getTopics());
    $topic_names = array();
    foreach ($topics as $topic) {
      $topic_names[] = $topic->getTitle();
    }
    if (! empty($topic_names)) {
      print '<p>Topics: ' . implode(', ', $topic_names);
    }

    // Outputs the HTML if requested.
    $html .= ob_get_contents();
    if ($output) {
      ob_flush();
    }
    else {
      ob_clean();
    }
  }

  ob_end_clean();
  return $html;
}
