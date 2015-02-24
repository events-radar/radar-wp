<?php

namespace Radar\Connect\Tests\Entity;

use Radar\Connect\Entity\Event;

class EventTest extends EntityTestCase {
  public function testRequestParse() {
    $response = $this->getMockResponse('event');
    $event = $this->parseResponse($response);

    $this->assertEquals(count($event), 1);
    $event = reset($event);
    // Basic properties
    $this->assertEquals($event->getUuid(), '69300100-b104-4c37-b651-48351543e8a6');
    $this->assertEquals($event->getVuuid(), 'a66a7c7d-5ed4-487e-92b8-ee876b91e2d6');
    $this->assertEquals($event->getInternalId(), '9171');
    $this->assertEquals($event->getInternalVid(), '9680');
    // Node level fields
    $this->assertEquals($event->apiUri(), 'https://new-radar.squat.net/api/1.0/node/69300100-b104-4c37-b651-48351543e8a6');
    $body_text = "<p>This is a handy event that site devs are using.</p>\n";
    $this->assertEquals($event->getBody(), $body_text);
    $this->assertEquals($event->getBodyRaw(), array('value' => $body_text, 'summary' => '', 'format' => 'rich_text_editor'));
    $this->assertEquals($event->getUrlView(),'https://new-radar.squat.net/en/event/amsterdam/joes-garage/2014-02-24/test-event');
    $this->assertEquals($event->getUrlEdit(),'https://new-radar.squat.net/en/node/9171/edit');
    $this->assertEquals($event->getStatus(), TRUE);
    $this->assertEquals($event->getCreated()->getTimestamp(),'1424807163');
    $this->assertEquals($event->getUpdated()->getTimestamp(),'1424807163');
    // Node level references
    $categories = $event->getCategories();
    $this->assertTrue($categories[0] instanceof \Radar\Connect\Entity\TaxonomyTerm);
    $this->assertEquals($categories[0]->apiUri(),'https://new-radar.squat.net/api/1.0/taxonomy_term/e85a688d-03ac-4008-a3cb-1adb7e8f718a');
    $topics = $event->getTopics();
    $this->assertTrue($topics[0] instanceof \Radar\Connect\Entity\TaxonomyTerm);
    $this->assertEquals($topics[0]->apiUri(), 'https://new-radar.squat.net/api/1.0/taxonomy_term/6c73cff2-9dc9-41db-a79e-f54bf4c010f7');
    // Simple fields.
    $this->assertTrue($event instanceof Event);
    $this->assertEquals($event->getTitle(), 'Test event');
    //$this->assertEquals($event->getImageRaw(), '');
    $this->assertEquals($event->getPrice(), 'Suggested donation €3');
    $this->assertEquals($event->getEmail(), 'joe@squat.net');
    $this->assertEquals($event->getLinkRaw(), array(array('url' => 'http://www.joesgarage.nl/', 'attributes' => array())));
    $this->assertEquals($event->getLink(), array('http://www.joesgarage.nl/'));
    $this->assertEquals($event->getPhone(), '01-12345');
    // Entity references.
    $price = $event->getPriceCategory();
    $this->assertTrue($price[0] instanceof \Radar\Connect\Entity\TaxonomyTerm);
    $this->assertEquals($price[0]->apiUri(), 'https://new-radar.squat.net/api/1.0/taxonomy_term/9d943d0c-e2bf-408e-9110-4bfb044f60c0');
    $this->assertEquals($price[1]->apiUri(), 'https://new-radar.squat.net/api/1.0/taxonomy_term/6f4101f4-cd9b-49f2-91a3-203d2b47a3ed');
    $groups = $event->getGroups();
    $this->assertTrue($groups[0] instanceof \Radar\Connect\Entity\Group);
    $this->assertEquals($groups[0]->apiUri(), 'https://new-radar.squat.net/api/1.0/node/0df4bcd7-54b4-4559-a960-60b5042d3d48');
    $raw_dates = $event->getDatesRaw();
    $this->assertEquals($raw_dates[0]['value'], '1393271100');
    $this->assertEquals($raw_dates[0]['time_end'], '2014-02-24T21:00:00+01:00');
    $dates = $event->getDates();
    $this->assertTrue($dates[0]['start'] instanceof \DateTime);
    $this->assertEquals($dates[0]['start']->getTimestamp(), '1393271100');
    $this->assertEquals($dates[0]['end']->getTimezone()->getName(), '+01:00');
    $locations = $event->getLocations();
    $this->assertTrue($locations[0] instanceof \Radar\Connect\Entity\Location);
    $this->assertEquals($locations[0]->apiUri(), 'https://new-radar.squat.net/api/1.0/location/3c58abc1-e095-4db5-996d-2a064cebb2d3');
  }
}
