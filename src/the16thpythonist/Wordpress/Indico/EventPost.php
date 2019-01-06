<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 04.01.19
 * Time: 08:01
 */

namespace the16thpythonist\Wordpress\Indico;

use the16thpythonist\Wordpress\Base\PostPost;
use the16thpythonist\Wordpress\Functions\PostUtil;


/**
 * Class EventPost
 *
 * CHANGELOG
 *
 * Added 04.01.2019
 *
 * @package the16thpythonist\Wordpress\Indico
 */
class EventPost extends PostPost
{
    public static $POST_TYPE;

    public static $REGISTRATION;

    public $ID;

    public $post;

    public $title;

    public $description;

    public $indico_id;

    public $published;

    public $start;

    public $url;

    public $creator;

    public $location;

    public $type;

    const DEFAULT_INSERT = array(
        'indico_id'     => '',
        'title'         => '',
        'description'   => '',
        'url'           => '',
        'published'     => '01-01-1900',
        'starting'      => '01-01-1900',
        'creator'       => '',
        'location'      => '',
        'type'          => 'event'
    );
    
    /**
     * EventPost constructor.
     *
     * CHANGELOG
     *
     * Added 04.01.2019
     *
     * @param $post_id
     */
    public function __construct($post_id)
    {
        $this->ID = $post_id;
        $this->post = get_post($post_id);

        // The title of the event is mapped as the title of the post and the description of the event is mapped as the
        // post content.
        $this->title = $this->post->post_title;
        $this->description = $this->post->post_content;
        // The publishing date of the event description on the indico website from which this post was derived is mapped
        // as the publishing date of the post on wordpress.
        $this->published = $this->post->post_date;

        // The starting date, the url and the indico ID are modeled as post meta data, because they are specific for
        // each event.
        $this->start = PostUtil::loadSinglePostMeta($this->ID, 'start_date');
        $this->url = PostUtil::loadSinglePostMeta($this->ID, 'url');
        $this->indico_id = PostUtil::loadSinglePostMeta($this->ID, 'indico_id');

        // The creator, type and location of the event are modeled as custom taxonomy terms, because in theory they
        // are all values, that multiple events can have in common. They are not loaded into attributes of this object,
        // but have to be accessed through methods, where they are computed "on the spot"
    }

    // ***************************************
    // GETTING THE VALUES MAPPED AS TAXONOMIES
    // ***************************************

    /**
     * Returns the name of the person, that has created the entry for this event on the indico platform
     *
     * CHANGELOG
     *
     * Added 04.01.2019
     *
     * @return string
     */
    public function getCreator() {
        return PostUtil::loadSingleTaxonomyString($this->ID, 'creator');
    }

    /**
     * Returns the string name of the type of event this is.
     *
     * CHANGELOG
     *
     * Added 04.01.2019
     *
     * @return string
     */
    public function getType() {
        return PostUtil::loadSingleTaxonomyString($this->ID, 'type');
    }

    /**
     * Returns the string name of the location, where this event is taking place
     *
     * CHANGELOG
     *
     * Added 04.01.2019
     *
     * @return string
     */
    public function getLocation() {
        return PostUtil::loadSingleTaxonomyString($this->ID, 'location');
    }

    // *************************
    // REGISTERING THE POST TYPE
    // *************************

    /**
     * Registers the post type in wordpress.
     *
     * CHANGELOG
     *
     * Added 04.01.2019
     *
     * @param string $post_type
     * @param string $class
     */
    public static function register(string $post_type, string $class=EventPostRegistration::class)
    {
        // Saving the exact string name of the post type in a static attribute so it can ve accessed by others as well
        // without actually having access to the object, only needing the namespace of the class.
        self::$POST_TYPE = $post_type;
        // The EventPostRegistration object handles the whole process of registering the post type in wordpress,
        // because that is a rather lengthy process and none of the concern of this mere wrapper class
        self::$REGISTRATION = new $class(self::$POST_TYPE);
        self::$REGISTRATION->register();
    }

    // ***************************************
    // STATIC METHODS FOR POST TYPE OPERATIONS
    // ***************************************

    /**
     * Inserts a new event post into the database.
     * Can have the following arguments:
     * - title:         The title of the event
     * - description:   Description of event
     * - published:     The date, when the info about the event was published on indico. Will also be the date
     *                  on which the wordpress post was published
     * - indico_id:     The ID of the event within the indico page
     * - starting:      The date, when the event starts
     * - url:           The url to the indico page about the event
     * - creator:       The name of who created the event on indico
     * - location:      Where the event is taking place
     * - type:          What type of event it is
     *
     * CHANGELOG
     *
     * Added 05.01.2018
     *
     * @param $args
     * @return int|\WP_Error
     */
    public static function insert($args) {
        $args = array_replace(self::DEFAULT_INSERT, $args);

        // Creating the array, which has to be passed to wordpress to actually create a post from the arguments about
        // the event. Some values are mapped as meta values but some have to be inserted as taxonomy terms separately
        $postarr = array(
            'post_type'         => self::$POST_TYPE,
            'post_title'        => $args['title'],
            'post_content'      => $args['description'],
            'post_status'       => 'publish',
            'post_date'         => $args['published'],
            'meta_input'        => array(
                'indico_id'         => $args['indico_id'],
                'start_date'        => $args['starting'],
                'url'               => $args['url']
            )
        );

        $post_id = wp_insert_post($postarr);



        return $post_id;
    }

    public static function createPostarr($args) {
        $postarr = array(
            'meta_input'    => array()
        );

        in_array('title', $args) ? $postarr['post_title'] = $args['title']: NULL;
    }

    public static function update($post_id, $args) {
        wp_update_post();

        return $post_id;
    }

    /**
     * Sets new values for the creator, the location and the type of an event post with the given post ID. Those are
     * the value which will be mapped as taxonomy terms of the post.
     *
     * CHANGELOG
     *
     * Added 05.01.2019
     *
     * @param string $post_id   The ID of the post, for which to set the values
     * @param string $creator   The new creator value
     * @param string $location  The new location
     * @param string $type      The new type
     */
    public static function setEventTerms(string $post_id, string $creator, string $location, string $type) {
        wp_set_object_terms($post_id, $creator, 'creator', false);
        wp_set_object_terms($post_id, $location,'location', false);
        wp_set_object_terms($post_id, $type, 'type', false);
    }

    /**
     * Given the indico ID of any event and the name of the indico site on which this event is on, this method will
     * return the URL directly to the page of the event.
     *
     * CHANGELOG
     *
     * Added 05.01.2019
     *
     * @param string $indico_id
     * @param string $site_name
     * @return string
     */
    public static function urlFromID(string $indico_id, string $site_name) {
        // The URL of a specific event is just a combination of the URL of the indico site and the indico ID. That is
        // why we need the URL of the given site first
        $site = KnownIndicoSites::getSite($site_name);
        $site_url = $site['url'];

        $event_url = $site_url . '/event/' . $indico_id . '/';
        return $event_url;
    }
}