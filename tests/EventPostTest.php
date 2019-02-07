<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 06.01.19
 * Time: 07:45
 */

use PHPUnit\Framework\TestCase;

use the16thpythonist\Wordpress\Indico\EventPost;
use the16thpythonist\Wordpress\Functions\PostUtil;

class EventPostTest extends TestCase
{
    public function testSubArrayMappingIsWorking() {
        $mapping = array(
            'test'  => 'hello/test',
            'hello' => 'hello/hello'
        );
        $data = array(
            'test'  => 1,
            'hello' => 2
        );
        $result = PostUtil::subArrayMapping($mapping, $data);

        $this->assertArrayHasKey('hello', $result);
        $this->assertArrayHasKey('test', $result['hello']);
        $this->assertEquals(1, $result['hello']['test']);
    }

    public function testSiteUrlExtractionIsWorking() {
        $url = 'https://indico.desy.de/indico/event/21067/';
        $site_url_expected = 'https://indico.desy.de/indico';

        $site_url = EventPost::siteUrlFromUrl($url);
        $this->assertEquals($site_url_expected, $site_url);
    }


}