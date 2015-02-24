<?php

namespace Radar\Connect\Tests\Entity;

use Radar\Connect\Entity\ListingsGroup;

class ListingsGroupTest extends EntityTestCase {
  public function testRequestParse() {
    $response = $this->getMockResponse('listings_group');
    $group = $this->parseResponse($response);

    $this->assertEquals(count($group), 1);
    $group = reset($group);
    // Basic properties
    $this->assertEquals($group->getUuid(), '9e43dac6-e1da-4f60-8428-de9f32ac9eb0');
    $this->assertEquals($group->getVuuid(), 'bf8e2d7e-3f35-44cf-aace-12aadba16948');
    $this->assertEquals($group->getInternalId(), '1599');
    $this->assertEquals($group->getInternalVid(), '8976');
    $this->assertEquals($group->getLanguage(), 'de');
    // Node level fields
    $this->assertEquals($group->apiUri(), 'https://new-radar.squat.net/api/1.0/node/9e43dac6-e1da-4f60-8428-de9f32ac9eb0');
    $body_text = "<p>Berliner Terminkalender für linke Subkultur und Politik</p>\n";
    $this->assertEquals($group->getBody(), $body_text);
    $this->assertEquals($group->getBodyRaw(), array('value' => $body_text, 'summary' => '', 'format' => 'rich_text_editor'));
    $this->assertEquals($group->getUrlView(),'https://new-radar.squat.net/en/node/1599');
    $this->assertEquals($group->getUrlEdit(),'https://new-radar.squat.net/en/node/1599/edit');
    $this->assertEquals($group->getStatus(), TRUE);
    $this->assertEquals($group->getCreated()->getTimestamp(),'1415355772');
    $this->assertEquals($group->getUpdated()->getTimestamp(),'1424428820');
    // Node level references
    $categories = $group->getCategories();
    $this->assertEquals(count($categories), 0);
    $topics = $group->getTopics();
    $this->assertEquals(count($topics), 0);
    // Simple fields.
    $this->assertTrue($group instanceof ListingsGroup);
    $this->assertEquals($group->getTitle(), 'Stressfaktor');
    //$this->assertEquals($group->getImageRaw(), '');
    //$this->assertEquals($group->getGroupLogoRaw(), '');
    $this->assertEquals($group->getEmail(), 'stressfaktor@squat.net');
    $this->assertEquals($group->getLinkRaw(), array(array('url' => 'http://stressfaktor.squat.net', 'title'=> '', 'attributes' => array())));
    $this->assertEquals($group->getLink(), array('http://stressfaktor.squat.net'));
    $this->assertEquals($group->getPhone(), null);
    // Entity references.
    $locations = $group->getLocations();
    $this->assertEquals(count($locations), 0);
    $listed_groups = $group->getGroupsListed();
    $this->assertEquals(count($listed_groups), 76);
    $this->assertEquals($listed_groups[0]->apiUri(), 'https://new-radar.squat.net/api/1.0/node/da296694-ae72-47a9-9073-e450143b9c58');
  }
}
