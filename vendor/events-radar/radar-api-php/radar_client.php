<?php

/**
 * @file
 *   Helper functions to create radar connect classes.
 */

require 'vendor/autoload.php';

use Radar\Connect\Connect;
use Radar\Connect\Filter;
use Radar\Connect\Cache;
use Guzzle\Http\Client;
use Doctrine\Common\Cache\FilesystemCache;
use Guzzle\Cache\DoctrineCacheAdapter;
use Guzzle\Plugin\Cache\CachePlugin;
use Guzzle\Plugin\Cache\DefaultCacheStorage;

/**
 * Helper function: create a radar connect client.
 *
 * @return Radar\Connect\Connect
 *   The connect client.
 */
function radar_client() {
  $guzzle = new Client();

  $cachePlugin = new CachePlugin(array(
    'storage' => new DefaultCacheStorage(
      new DoctrineCacheAdapter(
        new FilesystemCache(CACHE_PATH)
      )
    )
  ));

  // Add the cache plugin to the client object
  $guzzle->addSubscriber($cachePlugin);

  $connect = new Connect($guzzle);
  $cache = radar_cache();
  $connect->setCache(new Cache($cache));
  $connect->debug = FALSE;

  return $connect;
}

/**
 * Helper function: create a doctrine file system cache.
 *
 * Re-uses the same cache as set in radar_client() for
 * guzzle, adding a namespace.
 *
 * @return Doctrine\Common\Cache\FilesystemCache
 *   Doctrine file system cache.
 */
function radar_cache() {
  static $cache = NULL;

  if (is_null($cache)) {
    $cache = new FilesystemCache(CACHE_PATH);
    $cache->setNamespace('radar_');
  }

  return $cache;
}
