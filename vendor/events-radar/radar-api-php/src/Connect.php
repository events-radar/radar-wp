<?php

namespace Radar\Connect;

use Guzzle\Http\ClientInterface;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Radar\Connect\Entity\Entity;

class Connect {

  /**
   * @var ClientInterface Guzzle HTTP Client
   */
  protected $client;

  /**
   * @var Cache Doctrine cache for entities.
   */
  protected $cache;

  /**
   * @var string URL of API endpoint.
   */
  public $apiUrl;

  /**
   * @var bool Debug switch true for verbose.
   */
  public $debug;

  /**
   * Constructor.
   *
   * @param ClientInterface $client
   *   Guzzle HTTP Client.
   * @param array $configuration
   */
  public function __construct(ClientInterface $client, $configuration = array()) {
    $this->client = $client;
    $this->client->setDefaultOption('headers', array('Accept' => 'application/json'));

    if (!empty($configuration['api_url'])) {
      $this->apiUrl = $configuration['api_url'];
    }
    else {
      $this->apiUrl = 'https://new-radar.squat.net/api/1.0/';
    }
    $this->debug = !empty($configuration['debug']);
  }

  /**
   * For now also just allow direct access to guzzle itself.
   */
  public function __call($name, $arguments) {
    return call_user_func_array(array($this->client, $name), $arguments);
  }

  /**
   * Set a cache to store entities.
   *
   * @param \Radar\Connect\Cache $cache
   */
  public function setCache(Cache $cache) {
    $this->cache = $cache;
  }

  /**
   * Retrieve all fields for single entity.
   *
   * Entities can be partly loaded. Especially when just a reference on
   * an event or group. Use this to retrieve the full entity.
   * If there is a cache set, and the entity is still validly cached
   * this will be returned rather than making a new query.
   *
   * @param Entity $entity
   *   The partly loaded entity.
   *
   * @return Entity
   *   The loaded entity.
   */
  public function retrieveEntity(Entity $entity) {
    if (!empty($this->cache) && $this->cache->contains($entity)) {
      return $this->cache->fetch($entity);
    }
    $request = $this->client->get($entity->apiUri());
    $entity = $this->parseResponse($response);
    if (!empty($this->cache)) {
      $this->cache->save($entity);
    }
    return $entity;
  }

  /**
   * Retrieve all fields for multiple entities.
   *
   * As retrieveEntity(), but making multiple concurrent requests.
   *
   * @param Entity[] $entities
   *   Array of partly loaded entities.
   *
   * @return Entity[]
   *   Array of loaded entities.
   */
  public function retrieveEntityMultiple(&$entities) {
    $cached = array();
    if (!empty($this->cache)) {
      foreach($entities as $key => $entity) {
        if ($this->cache->contains($entity)) {
          $cached[] = $this->cache->fetch($entity);
          unset($entities[$key]);
        }
      }
    }

    $requests = array();
    foreach ($entities as $entity) {
      $requests[] = $this->client->get($entity->apiUri());
    }
    $retrieved = $this->retrieveMultiple($requests);

    if (!empty($this->cache)) {
      foreach ($retrieved as $entity) {
        $this->cache->save($entity);
      }
    }

    $entities = array_merge($cached, $retrieved);
    return $entities;
  }

  /**
   * TODO Insert or update an existing Entity.
   */
  public function putEntity(Entity $entity) {

  }



  /**
   * Prepare a request to retrieve events.
   *
   * @see self::retrieve()
   *
   * @param Filter $filter
   * @param array $fields
   *   A list of fields to load. Optional, default is most available fields.
   * @param int $limit
   *   How many events to return.
   *
   * @return \Guzzle\Http\Message\Request
   *   Request object to retrieve.
   */
  public function prepareEventsRequest(Filter $filter, $fields = array(), $limit = 500) {
    $request = $this->client->get($this->apiUrl . 'search/events.json');
    $query = $request->getQuery();
    $query->set('facets', $filter->getQuery());
    if (! empty($fields)) {
      // Always retrieve type.
      $fields = array_merge($fields, array('type'));
    }
    else {
      $fields = array(
        'title',
        'type',
        'uuid',
        'og_group_ref',
        'date_time',
        'offline',
        'category',
        'topic',
        'price',
        'link',
        'phone',
        'body',
        'image',
        'language',
        'created',
        'updated',
        'view_url',
      );
    }
    $query->set('fields', $fields);
    $query->set('limit', $limit);
    return $request;
  }

  /**
   * Prepare a request to retrieve groups.
   *
   * @see self::retrieve()
   *
   * @param Filter $filter
   * @param array $fields
   *   A list of fields to load. Optional, default is most available fields.
   * @param int $limit
   *   How many groups to return.
   *
   * @return \Guzzle\Http\Message\Request
   *   Request object to retrieve.
   */
  public function prepareGroupsRequest(Filter $filter, $fields = array(), $limit = 500) {
    $request = $this->client->get($this->apiUrl . 'search/groups.json');
    $query = $request->getQuery();
    $query->set('facets', $filter->getQuery());
    if (! empty($fields)) {
      $fields += array('type');
    }
    else {
      $fields = array(
        'title',
        'type',
        'category',
        'offline',
        'topic',
        'body',
        'email',
        'weblink',
        'offline',
        'opening_times',
        'phone',
        'view_url',
      );
    }
    $query->set('fields', $fields);
    $query->set('limit', $limit);
    return $request;
  }

  /**
   * Retrieve entities from a prepared request.
   *
   * @param \Guzzle\Http\Message\RequestInterface $request
   *
   * @return Entity[]
   */
  public function retrieve(RequestInterface $request) {
    $response = $this->client->send($request);
    if ($this->debug) {
      var_export($response->getHeaders());
      var_export($response->getBody());
    }
    return $this->parseResponse($response);
  }

  /**
   * Retrieve entities from multiple prepared requests.
   *
   * Results are merged into one entity array.
   *
   * @param \Guzzle\Http\Message\RequestInterface[] $requests
   *
   * @return Entity[]
   */
  public function retrieveMultiple($requests) {
    try {
      $responses = $this->client->send($requests);
    }
    catch (MultiTransferException $e) {
      foreach ($e->getFailedRequests() as $request) {
      }

      foreach ($e->getSuccessfulRequests() as $request) {
      }
    }

    $items = array();
    foreach ($responses as $response) {
      $items = array_merge($items, $this->parseResponse($response));
    }
    return $items;
  }

  /**
   * Parse a response from the client.
   *
   * TODO this doesn't need to be in here.
   */
  protected function parseResponse(Response $response) {
    $items = array();

    $content = $response->json();
    if (isset($content['type'])) {
      $class = __NAMESPACE__ . '\\Entity\\' . Entity::className($content['type']);
      $content['apiBase'] = $this->apiUrl;
      $items[] = new $class($content);
    }
    else {
      foreach ($content as $key => $item) {
        $class = __NAMESPACE__ . '\\Entity\\' . Entity::className($item['type']);
        $item['apiBase'] = $this->apiUrl;
        $items[] = new $class($item);
      }
    }

    return $items;
  }

}
