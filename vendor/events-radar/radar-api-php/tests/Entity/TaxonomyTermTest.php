<?php

namespace Radar\Connect\Tests\Entity;

use Radar\Connect\Entity\TaxonomyTerm;

class TaxonomyTermTest extends EntityTestCase {
  public function testRequestParse() {
    $response = $this->getMockResponse('taxonomy_term');
    $term = $this->parseResponse($response);

    $this->assertEquals(count($term), 1);
    $term = reset($term);
    $this->assertTrue($term instanceof TaxonomyTerm);

    $this->assertEquals($term->getTitle(), 'action/protest/camp');
    $this->assertEquals($term->apiUri(), 'https://new-radar.squat.net/api/1.0/taxonomy_term/e85a688d-03ac-4008-a3cb-1adb7e8f718a');
    $this->assertEquals($term->getUuid(), 'e85a688d-03ac-4008-a3cb-1adb7e8f718a');
    $this->assertEquals($term->getVuuid(), null);
    $this->assertEquals($term->getInternalId(), 7);
    $this->assertEquals($term->getInternalVid(), null);
    $this->assertEquals($term->getNodeCount(), 10);
    $this->assertEquals($term->getVocabulary(), 'category');
   }
}
