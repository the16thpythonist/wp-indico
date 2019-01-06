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

class TestEventPost extends TestCase
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

        var_dump($result);

        $this->assertArrayHasKey('hello', $result);
        $this->assertArrayHasKey('test', $result['hello']);
        $this->assertEquals(1, $result['hello']['test']);
    }

    public function testEventPostPostarrCreationIdeaWorks() {
        $args = array(
            'title'     => 'test'
        );
        $postarr = EventPost::createPostarr($args);
        $this->assertTrue(array_key_exists('post_title', $postarr));

        $args = array();
        $postarr = EventPost::createPostarr($args);
        $this->assertFalse(array_key_exists('post_title', $postarr));
    }
}