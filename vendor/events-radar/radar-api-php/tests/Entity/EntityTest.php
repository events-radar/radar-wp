<?php

namespace Radar\Connect\Tests\Entity;

use Guzzle\Tests\GuzzleTestCase;
use Radar\Connect\Connect;
use Guzzle\Http\Client;

abstract class EntityTestCase extends GuzzleTestCase {
  /**
   * @see Connect\parseResponse
   */
  protected function parseResponse($response) {
    // Reflection... clue... it shouldn't be quite like that!
    $reflectionClient = new \ReflectionClass('Radar\Connect\Connect');
    $parseResponse = $reflectionClient->getMethod('parseResponse');
    $parseResponse->setAccessible(true);
     // Argh fix me.
    $client = new Connect(new Client());
    return $parseResponse->invokeArgs($client, array($response));
  }
}
