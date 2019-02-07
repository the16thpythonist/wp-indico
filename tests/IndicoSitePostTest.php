<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 07.02.19
 * Time: 11:16
 */

use PHPUnit\Framework\TestCase;

use the16thpythonist\Wordpress\Indico\IndicoSitePost;
use the16thpythonist\Wordpress\Indico\IndicoSitePostRegistration;

class IndicoSitePostTest extends TestCase
{
    // This array will be used to insert a default IndicoSite post into the data base
    const DEFAULT_INSERT_ARGS = array(
        'name'          => 'my indico site',
        'url'           => 'https://google.com',
        'key'           => 'SECRET',
        'categories'    => array(1, 2)
    );

    // This array will have to be created as "postarr" array for the wp insert post operation
    const DEFAULT_POSTARR = array(
        'post_title'    => 'my indico site',
        'meta_input'    => array(
            'url'           => 'https://google.com',
            'key'           => 'SECRET',
            'categories'    => array(1, 2)
        )
    );

    // ****************************
    // SETUP CODE FOR FURTHER TESTS
    // ****************************


    // *************************
    // CHECKING THE REGISTRATION
    // *************************

    public function testIndicoSitesPostTypeExists() {
        $exists = post_type_exists(IndicoSitePost::$POST_TYPE);
        $this->assertTrue($exists);
    }

    // ********************
    // NON DATABASE METHODS
    // ********************

    public function testPostarrCreationWorks() {
        $postarr = IndicoSitePost::createPostarr(self::DEFAULT_INSERT_ARGS);
        $this->assertArraySubset(self::DEFAULT_POSTARR, $postarr);
    }

    // ****************
    // DATABASE METHODS
    // ****************

    public function testWrapperInsertWorks() {
        $post_id = IndicoSitePost::insert(self::DEFAULT_POSTARR);

        $post = get_post($post_id);
        // $this->assertEquals(self::DEFAULT_INSERT_ARGS['name'], $post->post_title);
    }

}