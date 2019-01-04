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
    public static function register(string $post_type, string $class='EventPostRegistration')
    {
        // Saving the exact string name of the post type in a static attribute so it can ve accessed by others as well
        // without actually having access to the object, only needing the namespace of the class.
        self::$POST_TYPE = $post_type;
        // The EventPostRegistration object handles the whole process of registering the post type in wordpress,
        // because that is a rather lengthy process and none of the concern of this mere wrapper class
        self::$REGISTRATION = new $class(self::$POST_TYPE);
        self::$REGISTRATION->register();
    }
}