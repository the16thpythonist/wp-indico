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
class EventPost
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
     * Retruns an array of all the EventPost objects for every post of the type "event"
     *
     * CHANGELOG
     *
     * Added 06.01.2019
     *
     * @return array
     */
    public static function getAll() {
        $posts = self::getAllPosts();

        $event_posts = array();
        foreach ($posts as $post) {
            // Creating a new wrapper object from the ID of the post and then adding it to the new array.
            $event_posts[] = new EventPost($post->ID);
        }
        return $event_posts;
    }

    /**
     * Returns an array of all the WP_Post objects for posts of the custom type "event"
     * ! They are not wrapped yet. Those will just be the normal wordpress post objects
     *
     * CHANGELOG
     *
     * Added 06.01.2019
     *
     * @return array
     */
    public static function getAllPosts() {
        $args = array(
            'post_type'         => self::$POST_TYPE,
            'post_status'       => 'publish',
            'posts_per_page'    => -1
        );
        $query = new \WP_Query($args);
        $posts = $query->get_posts();

        return $posts;
    }

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

        // This method will add additional derived arguments to the array, which were computed from the given ones.
        $args = self::extendPostArgs($args);

        // Creating the array, which has to be passed to wordpress to actually create a post from the arguments about
        // the event. Some values are mapped as meta values but some have to be inserted as taxonomy terms separately
        $postarr = self::createPostarr($args);
        $postarr['post_type'] = self::$POST_TYPE;

        $post_id = wp_insert_post($postarr);

        // This function will insert the values of the arguments that were mapped as taxonomies
        self::setEventTerms($post_id, $args);

        return $post_id;
    }

    /**
     * Given the wordpress post ID of the post to change and an array with the arguments, that are supposed to be
     * changed, this function will update the wordpress post accordingly.
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
     * Added 06.01.2019
     *
     * @param $post_id
     * @param $args
     * @return mixed
     */
    public static function update($post_id, $args) {

        // This method computes additional derived arguments from the ones given and adds them to the arguments array
        $args = self::extendPostArgs($args);

        // Computes the actual array, that has to be passed to the wordpress function to actually create a new post
        // from the array, that just specifies top level attributes of the event.
        $postarr = self::createPostarr($args);
        $postarr['ID'] = $post_id;

        echo wp_update_post($postarr);

        // This function will insert the values of the arguments that were mapped as taxonomies
        self::setEventTerms($post_id, $args);

        return $post_id;
    }

    /**
     * This function extends the arguments array passed to insert or update a post with the additional values, which
     * need to be computed/derived from the given ones.
     *
     * CHANGELOG
     *
     * Added 06.01.2019
     *
     * @param array $args
     * @return array
     */
    public static function extendPostArgs(array $args) {
        // The site URL is not a native argument, that can be passed. It is a derived one, which is computed directly
        // from the URL of the specific event.
        if (array_key_exists('url', $args)) {
            $args['site_url'] = self::siteUrlFromUrl($args['url']);
        }
        return $args;
    }

    /**
     * Given the argument array, containing the key value pairs of the event specific arguments, this will return the
     * postarr array, which has to be passed to the wordpress insert post function to actually create a post based on
     * the given arguments.
     * This postarr however will only contain the sccording entries for the arguments, that are actually given within
     * the args array. This means this function can also be used for a post update.
     *
     * CHANGELOG
     *
     * Added 06.01.2019
     *
     * @param array $args   The event specific arguments
     * @return array
     */
    public static function createPostarr(array $args) {
        $mapping = array(
            'title'         => 'post_title',
            'description'   => 'post_content',
            'published'     => 'post_date',
            'indico_id'     => 'meta_input/indico_id',
            'starting'      => 'meta_input/start_date',
            'url'           => 'meta_input/url',
            'site_url'      => 'meta_input/site_url'
        );
        $postarr = PostUtil::subArrayMapping($mapping, $args);
        $postarr['post_status'] = 'publish';

        return $postarr;
    }

    /**
     * Sets new values for the creator, the location and the type of an event post with the given post ID. Those are
     * the value which will be mapped as taxonomy terms of the post.
     *
     * CHANGELOG
     *
     * Added 05.01.2019
     *
     * Changed 06.01.2019
     * Generalized it. Changed the three concrete parameters to one array parameter. Now the values dont have to
     * replaced, they are only replaced if their keys are contained within the given array.
     *
     * @param string $post_id   The ID of the post, for which to set the values
     * @param array $args
     */
    public static function setEventTerms(string $post_id, array $args) {
        $taxonomy_mapping = array(
            'creator'       => 'creator',
            'location'      => 'location',
            'type'          => 'type'
        );
        foreach ($taxonomy_mapping as $key => $taxonomy) {
            if (array_key_exists($key, $args)) {
                wp_set_object_terms($post_id, $args[$key], $taxonomy);
            }
        }
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

    /**
     * Given the url of a specific event, this function will return the URL of the indico sites main page
     *
     * NOTE:
     * This implementation may be replaced using regular expressions?
     *
     * CHANGELOG
     *
     * Added 06.01.2019
     *
     * @param string $url   The url of the event
     * @param int $depth    DEFAULT is 3.
     * @return string
     */
    public static function siteUrlFromUrl(string $url, int $depth=3) {
        // Every specific URL for a single event on an indico site is built in the same structure: It is the base url
        // followed by /event/{actual_id}. We will be extracting the site url by just cutting of after the second last
        // slash (/) has occurred.
        $url_parts = explode('/', $url);
        $url_parts_sliced = array_slice($url_parts, 0, count($url_parts) - $depth, FALSE);
        $site_url = implode('/', $url_parts_sliced);
        return $site_url;
    }
}