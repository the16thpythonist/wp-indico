<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 04.02.19
 * Time: 14:18
 */

namespace the16thpythonist\Wordpress\Indico;

use the16thpythonist\Wordpress\Functions\PostUtil;

/**
 * Class IndicoSitePost
 *
 * CHANGELOG
 *
 * Added 04.02.2019
 *
 * @package the16thpythonist\Wordpress\Indico
 */
class IndicoSitePost
{
    // *****************
    // STATIC ATTRIBUTES
    // *****************

    public static $POST_TYPE;

    public static $REGISTRATION;

    const DEFAULT_INSERT = array(
        'name'          => '',
        'key'           => '',
        'url'           => '',
        'categories'    => array()
    );

    // *************************
    // RELATED TO WORDPRESS POST
    // *************************

    public $ID;

    public $post;

    // *****************
    // CUSTOM ATTRIBUTES
    // *****************

    public $name;

    public $key;

    public $url;

    public $categories;


    /**
     * IndicoSitePost constructor.
     *
     * CHANGELOG
     *
     * Added 04.02.2019
     *
     * @param $post_id
     */
    public function __construct($post_id)
    {
        // The ID attribute is the same as the post ID of the post object on which this wrapper is based and the "post"
        // attribute contains this post object.
        $this->ID = $post_id;
        $this->post = get_post($this->ID);

        // The title of the post object is mapped to the "name" attribute of an indico site.
        $this->name = $this->post->post_title;

        // The "key" api key attribute for the indico site and the "url" attribute are mapped as single string meta
        // value to the post object
        $this->key = PostUtil::loadSinglePostMeta($this->ID, 'key');
        $this->url = PostUtil::loadSinglePostMeta($this->ID, 'url');

        // The categories are mapped as a single post meta, but the value saved in the single meta field is an array
        $this->categories = PostUtil::loadSinglePostMeta($this->ID, 'categories');
    }

    // *************************
    // REGISTERING THE POST TYPE
    // *************************

    /**
     * Registers the post type in wordpress.
     *
     * CHANGELOG
     *
     * Added 04.02.2019
     *
     * @param string $post_type
     * @param string $class
     */
    public static function register(string $post_type, string $class=IndicoSitePostRegistration::class) {
        self::$POST_TYPE = $post_type;

        self::$REGISTRATION = new $class(self::$POST_TYPE);
        self::$REGISTRATION->register();
    }

    // **********************************************
    // STATIC METHODS FOR GLOBAL POST TYPE OPERATIONS
    // **********************************************

    // ***************
    // GETTING OBJECTS
    // ***************

    /**
     * Returns an array containing IndicoSitePost objects for all the sites saved on the wordpress system.
     *
     * CHANGELOG
     *
     * Added 04.02.2019
     *
     * @return array
     */
    public static function getAll() {
        // We will be getting the array, which contains all the WP_Post objects for the posts of the "indico site"
        // type, and then from this array we will build a new array of IndicoSitePost objects, by wrapping the wordpress
        // posts
        $posts = self::getAllPosts();

        $event_posts = array_map(function ($post) {return new IndicoSitePost($post->ID);}, $posts);
        return $event_posts;
    }

    /**
     * Returns an array with all the WP_Post objects for all the posts of the "indico site" type.
     *
     * CHANGELOG
     *
     * Added 04.02.2019
     *
     * @return array
     */
    public static function getAllPosts() {

        // This array specifies a wordpress query, which will return all the wordpress post object with the
        // "indico site" post type
        $args = array(
            'post_type'         => self::$POST_TYPE,
            'post_status'       => 'any',
            'posts_per_page'    => -1
        );
        $query = new \WP_Query($args);
        $posts = $query->get_posts();

        return $posts;
    }

    /**
     * If a site with the given name exists, this function will return the IndicoSitePost wrapper for that post. In
     * case it does not exist, FALSE will be returned
     *
     * CHANGELOG
     *
     * Added 04.02.2019
     *
     * @param string $name
     * @return bool|IndicoSitePost
     */
    public static function getSite(string $name) {
        // Here we create a wordpress query for the desired post by using the search argument "s"
        $args = array('s' => $name);
        $query = self::buildQuery($args);

        if ($query->have_posts()) {

            // Using the very first post (which matches the given search best) to return.
            $post = $query->get_posts()[0];
            $indico_site = new IndicoSitePost($post->ID);
            return $indico_site;

        } else {
            return FALSE;
        }
    }

    /**
     * Builds a WP_Query object based on some common parameters for the "indico site" post type and the additional
     * passed parameters of the "args" array and returns that object.
     *
     * CHANGELOG
     *
     * Added 04.02.2019
     *
     * @param array $args
     * @return \WP_Query
     */
    protected static function buildQuery(array $args) {

        // Here we build an array, that specifies the arguments, which all queries for a indico site have in common and
        // then this base array gets updated with the additional arguments given as parameter.
        $query_args = array(
            'post_type'         => self::$POST_TYPE,
            'post_status'       => 'any',
            'posts_per_page'    => -1,
        );
        $query_args = array_replace($query_args, $args);

        // Returning the WP_Query object based on the resulting parameter array
        $query = new \WP_Query($query_args);
        return $query;
    }

    // *****************
    // MODIFYING OBJECTS
    // *****************

    /**
     * This method is used to create a new IndicoSitePost.
     * The args array has to have the following arguments:
     * - name:          A unique string name with which this indico site is being identified in the future
     * - url:           The string url to the main page of the indico site to be represented
     * - key:           The string API key, which has been acquired for the site and which will be used to make restful
     *                  requests to that site in the future
     * - categories:    An array containing int|string IDs of all the categories that are supposed to be observed for
     *                  for the given site.
     *
     * CHANGELOG
     *
     * Added 04.02.2019
     *
     * @param $args
     */
    public static function insert(array $args) {
        // In case there are not all necessary arguments given, the rest is being supplemented with the default insert
        // arguments specified in "DEFAULT_INSERT_ARGS"
        $args = array_replace(self::DEFAULT_INSERT, $args);

        // Creating the "postarr", which is the array which has to be actually used for the wordpress insert function
        // from the arguments array
        $postarr = self::createPostarr($args);
        $postarr['post_type'] = self::$POST_TYPE;

        wp_insert_post($postarr);
    }

    /**
     * This method is used to update an existing IndicoSitePost with new values, given the wordpress ID of the post.
     * The args array has to have the following arguments:
     * - name:          A unique string name with which this indico site is being identified in the future
     * - url:           The string url to the main page of the indico site to be represented
     * - key:           The string API key, which has been acquired for the site and which will be used to make restful
     *                  requests to that site in the future
     * - categories:    An array containing int|string IDs of all the categories that are supposed to be observed for
     *                  for the given site.
     *
     * CHANGELOG
     *
     * Added 04.02.2019
     *
     * @param string $post_id
     * @param array $args
     */
    public static function updatePost(string $post_id, array $args) {

        // Creating the "postarr", which is the array which has to be actually used for the wordpress insert function
        // from the arguments array
        $postarr = self::createPostarr($args);
        // To update the post identified by the given post id it has to be added as an additional entry to the postarr
        $postarr['ID'] = $post_id;

        wp_update_post($postarr);
    }

    /**
     * This method is used to update an existing IndicoSitePost with new values, given the name of the indico site.
     * The args array has to have the following arguments:
     * - name:          A unique string name with which this indico site is being identified in the future
     * - url:           The string url to the main page of the indico site to be represented
     * - key:           The string API key, which has been acquired for the site and which will be used to make restful
     *                  requests to that site in the future
     * - categories:    An array containing int|string IDs of all the categories that are supposed to be observed for
     *                  for the given site.
     *
     * CHANGELOG
     *
     * Added 04.02.2019
     * @param string $name
     * @param array $args
     */
    public static function updateSite(string $name, array $args) {
        // The "getSite" method returns a FALSE in case there is not even a site with that name, that is why we do the
        // of check for it.
        $indico_site = self::getSite($name);
        if ($indico_site) {
            // Calling the specific method for the post object itself, now that we know the post ID
            self::updatePost($indico_site->ID, $args);
        }
    }

    /**
     * Deletes the post with the given wordpress post ID
     *
     * CHANGELOG
     *
     * Added 04.02.2019
     */
    public static function deletePost(string $post_id) {
        wp_delete_post($post_id);
    }

    /**
     * Deletes the indico site post with the given name
     *
     * CHANGELOG
     *
     * Added 04.02.2019
     *
     * @param string $name
     */
    public static function deleteSite(string $name) {
        // The "getSite" method returns a FALSE in case there is not even a site with that name, that is why we do the
        // of check for it.
        $indico_site = self::getSite($name);
        if ($indico_site) {
            self::deletePost($indico_site->ID);
        }
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
     * Added 04.02.2019
     *
     * @param array $args
     * @return array
     */
    protected static function createPostarr(array $args) {
        $mapping = array(
            'name'          => 'post_title',
            'url'           => 'meta_input/url',
            'key'           => 'meta_input/key',
            'categories'    => 'meta_input/categories'
        );
        $postarr = PostUtil::subArrayMapping($mapping, $args);
        $postarr['post_status'] = 'publish';

        return $postarr;
    }

}