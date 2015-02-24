<?php

namespace Radar\Connect\Tests\Entity;

use Radar\Connect\Entity\Group;

class GroupTest extends EntityTestCase {
  public function testRequestParse() {
    $response = $this->getMockResponse('group');
    $group = $this->parseResponse($response);

    $this->assertEquals(count($group), 1);
    $group = reset($group);
    // Basic properties
    $this->assertEquals($group->getUuid(), '0df4bcd7-54b4-4559-a960-60b5042d3d48');
    $this->assertEquals($group->getVuuid(), 'c6df91b9-58bd-4a5f-a52e-64ec18f267f0');
    $this->assertEquals($group->getInternalId(), '41');
    $this->assertEquals($group->getInternalVid(), '8935');
    // Node level fields
    $this->assertEquals($group->apiUri(), 'https://new-radar.squat.net/api/1.0/node/0df4bcd7-54b4-4559-a960-60b5042d3d48');
    $body_text = "<p>Joe's Garage is een ontmoetingsplek voor al dan niet krakers uit de transvaalbuurt en omstreken.</p>\n";
    $this->assertEquals($group->getBody(), $body_text);
    $this->assertEquals($group->getBodyRaw(), array('value' => $body_text, 'summary' => '', 'format' => 'rich_text_editor'));
    $this->assertEquals($group->getUrlView(),'https://new-radar.squat.net/nl/amsterdam/joes-garage?language=nl');
    $this->assertEquals($group->getUrlEdit(),'https://new-radar.squat.net/nl/node/41/edit?language=nl');
    $this->assertEquals($group->getStatus(), TRUE);
    $this->assertEquals($group->getCreated()->getTimestamp(),'1409775185');
    $this->assertEquals($group->getUpdated()->getTimestamp(),'1424352703');
    // Node level references
    $categories = $group->getCategories();
    $this->assertEquals(count($categories), 6);
    $this->assertTrue($categories[0] instanceof \Radar\Connect\Entity\TaxonomyTerm);
    $this->assertEquals($categories[0]->apiUri(),'https://new-radar.squat.net/api/1.0/taxonomy_term/e97f372b-29bc-460b-bff6-35d2462411ff?language=nl');
    $topics = $group->getTopics();
    $this->assertTrue($topics[0] instanceof \Radar\Connect\Entity\TaxonomyTerm);
    $this->assertEquals($topics[0]->apiUri(), 'https://new-radar.squat.net/api/1.0/taxonomy_term/82f00d0a-03df-40ec-a06d-67b875675858?language=nl');
    // Simple fields.
    $this->assertTrue($group instanceof Group);
    $this->assertEquals($group->getTitle(), 'Joe\'s Garage');
    //$this->assertEquals($group->getImageRaw(), '');
    //$this->assertEquals($group->getGroupLogoRaw(), '');
    $this->assertEquals($group->getEmail(), 'joe@squat.net');
    $this->assertEquals($group->getLinkRaw(), array(array('url' => 'http://www.joesgarage.nl/', 'attributes' => array())));
    $this->assertEquals($group->getLink(), array('http://www.joesgarage.nl/'));
    $this->assertEquals($group->getPhone(), null);
    $opening_times = "<p>Maandag: 19u <strong>Volkseten Vegazulu</strong></p>\n<p>Dinsdag: 11u/15u <strong>Kraakspreekuur, (daarna is er een borrel)</strong></p>\n<p>Dinsdag: 20u/21u30 <strong>Kraakspreekuur Oost</strong></p>\n<p>Woensdag: 15u/18u <strong>Lonely Collective Day Cafe</strong></p>\n<p>Donderdag: 19u <strong>Volkseten Vegazulu</strong></p>\n<p>Zaterdag: 14u/18u <strong>Weggeefwinkel</strong></p>\n<p>Zondag: 20u <strong>Filmavonden/Infoavonden</strong></p>\n";
    $this->assertEquals($group->getOpeningTimesRaw(), array('value' => $opening_times, 'format' => 'rich_text_editor'));
    $this->assertEquals($group->getOpeningTimes(), $opening_times);
    // Entity references.
    $locations = $group->getLocations();
    $this->assertTrue($locations[0] instanceof \Radar\Connect\Entity\Location);
    $this->assertEquals($locations[0]->apiUri(), 'https://new-radar.squat.net/api/1.0/location/3c58abc1-e095-4db5-996d-2a064cebb2d3?language=nl');
  }
}
