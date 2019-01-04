<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 04.01.19
 * Time: 10:40
 */

namespace the16thpythonist\Wordpress\Indico;

use the16thpythonist\Wordpress\Base\Metabox;

/**
 * Class EventPostFetchMetabox
 *
 * CHANGELOG
 *
 * Added 04.01.2019
 *
 * @package the16thpythonist\Wordpress\Indico
 */
class EventPostFetchMetabox implements Metabox
{

    const ID = 'event-post-fetch-metabox';
    const TITLE = 'Request Event from Indico';

    // ***********************************
    // DISPLAYING THE HTML FOR THE METABOX
    // ***********************************

    /**
     * This method echos the whole html code, that makes up the display of the metabox.
     *
     * CHANGELOG
     *
     * Added 04.01.2019
     *
     * @param \WP_Post $post
     * @return mixed|void
     */
    public function display($post)
    {
        echo "Test";
    }

    public function load($post)
    {
        // TODO: Implement load() method.
    }

    // *******************************
    // THE REGISTRATION OF THE METABOX
    // *******************************

    /**
     * Actually calls the necessary functions to hook in the other methods containing the registration code into the
     * wordpress system. Also registers Ajax callbacks.
     *
     * CHANGELOG
     *
     * Added 04.01.2019
     *
     * @return mixed|void
     */
    public function register()
    {
        // The metabox itself
        add_action('add_meta_boxes', array($this, 'registerMetabox'));
    }

    /**
     * Registers the metabox to a callback and tells wordpress to associate it with the "Event" post type edit screen.
     *
     * CHANGELOG
     *
     * Added 04.01.2019
     */
    public function registerMetabox() {
        add_meta_box(
            self::ID,
            self::TITLE,
            array($this, 'load'),
            EventPost::$POST_TYPE,
            'normal',
            'high'
        );
    }

    // ****************************
    // SAVING DATA FROM THE METABOX
    // ****************************

    public function save($post_id)
    {
        // TODO: Implement save() method.
    }
}