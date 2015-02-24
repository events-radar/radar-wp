<?php

namespace Radar\Connect\Tests\Entity;

use Radar\Connect\Entity\Location;

class LocationTest extends EntityTestCase {
  public function testRequestParse() {
    $response = $this->getMockResponse('location');
    $location = $this->parseResponse($response);

    $this->assertEquals(count($location), 1);
    $location = reset($location);
    $this->assertTrue($location instanceof Location);

    $this->assertEquals('X-B-Liebig Liebigstr. 34  Berlin Germany', $location->getTitle());
    $this->assertEquals('X-B-Liebig, Liebigstr. 34, Berlin', $location->getAddress());
    $this->assertEquals('X-B-Liebig, 10247, DE', $location->getAddress(array('name_line', 'postal_code', 'country')));
    $this->assertEquals('U-Bhf. Frankfurter Tor', $location->getDirections());
    $point = $location->getLocation();
    $this->assertEquals('POINT (13.4570431 52.5179561)', $point->out('wkt'));
  }
}
