<?php

/**
 * @file
 *   Radar entity cache.
 */

namespace Radar\Connect;

use Doctrine\Common\Cache\Cache as CacheInterface;
use Radar\Connect\Entity\Entity;

class Cache {
  /**
   * @var CacheInteface Doctrine cache.
   */
  protected $cache;

  public function __construct(CacheInterface $cache) {
    $this->cache = $cache;
  }

  public function contains(Entity $entity) {
    return $this->cache->contains($entity->apiUri());
  }

  public function fetch(Entity $entity) {
    return $this->cache->fetch($entity->apiUri());
  }

  public function save(Entity $entity) {
    // TODO Make configurable.
    $ttl = array(
      'group' => 60 * 60,
      'listings_group' => 60 * 60,
      'event' => 60 * 5,
      'location' => 60 * 60 * 24,
      'taxonomy_term' => 60 * 60 * 24 * 30,
    );
    return $this->cache->save($entity->apiUri(), $entity, $ttl[$entity->type]);
  }

  public function delete(Entity $entity) {
    return $this->cache->delete($entity->apiUri());
  }
}
