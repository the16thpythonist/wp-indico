<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 04.01.19
 * Time: 09:33
 */

namespace the16thpythonist\Wordpress\Indico;

use the16thpythonist\Wordpress\Base\PostRegistration;
use the16thpythonist\Wordpress\Functions\PostUtil;

/**
 * Class EventPostRegistration
 *
 * CHANGELOG
 *
 * Added 04.01.2019
 *
 * @package the16thpythonist\Wordpress\Indico
 */
class EventPostRegistration implements PostRegistration
{
    public $post_type;

    public $meta_boxes = array();

    const LABEL = 'Event';
    const ICON = 'dashicons-groups';
    const TAXONOMIES = array(
        'creator'   => array(
            'label'     => 'Creator',
            'public'    => true
        ),
        'type'      => array(
            'label'     => 'Type',
            'public'    => true,
        ),
        'location'  => array(
            'label'     => 'Location',
            'public'    => true
        )
    );

    /**
     * EventPostRegistration constructor.
     *
     * CHANGELOG
     *
     * Added 04.01.2019
     *
     * @param $post_type
     */
    public function __construct($post_type)
    {
        // The name for the post type has to be specified as a parameter. It will not be hardcoded. If it has to be
        // used it can be accessed as a static attribute of the facade for this package.
        $this->post_type = $post_type;
    }

    /**
     * Returns the exact string name, under which the "event" post type is registered within wordpress.
     *
     * @return mixed
     */
    public function getPostType()
    {
        return $this->post_type;
    }

    // *********************************
    // THE REGISTRATION OF THE POST TYPE
    // *********************************

    /**
     * Actually hooks in the functions, which do the registration into the init hook
     *
     * CHANGELOG
     *
     * Added 04.01.2019
     */
    public function register() {
        // Registering the post type first
        add_action('init', array($this, 'registerPostType'));

        // All the taxonomies
        add_action('init', array($this, 'registerTaxonomies'));

        add_action('save_post', array($this, 'savePost'));

        // The metaboxes are registered by creating their own Metabox objects, which handle the registration, because
        // that is a lengthy process and none of the the concern of this class.
        // Thus this method does not need to be hooked in to anything, but rather executed right away.
        $this->registerMetaboxes();
    }

    /**
     * Registers all the meta boxes, that are needed for the "Event" post type. The metaboxes will appear in the
     * "edit post" screen for a "Event" post.
     *
     * CHANGELOG
     *
     * Added 04.01.2019
     */
    public function registerMetaboxes() {
        // This metabox will be used to instantly create a single new event by directly fetching it from an Indico site
        // The actual instance of the Metabox object will be stored in an array, as it may be needed by other code
        $fetch_metabox = new EventPostFetchMetabox();
        $fetch_metabox->register();
        $this->meta_boxes[] = $fetch_metabox;
    }

    /**
     * CHANGELOG
     *
     * Added 04.01.2019
     *
     * Changed 04.01.2019
     * Added an if statement, that first checks if the post type exists already before creating a new one. This will
     * prevent errors, when the overall register is called in multiple plugins.
     */
    public function registerPostType() {
        if (!post_type_exists($this->post_type)) {
            // Creating the arguments array, which specifies the details of what kind of post type it is supposed to be
            $args = array(
                'label'                 => 'Event',
                'description'           => 'Describes an upcoming indico event',
                'public'                => true,
                'publicly_queryable'    => true,
                'show_ui'               => true,
                'menu_position'         => 5,
                'map_meta_cap'          => true,
                'supports'              => array(
                    'title',
                    'editor',
                    'custom-fields'
                ),
                'menu_icon'             => self::ICON,
            );
            register_post_type($this->post_type, $args);
        }

    }

    /**
     * Registers the custom taxonomies for the creator, type and location of the event in wordpress
     *
     * CHANGELOG
     *
     * Added 04.01.2019
     *
     * Changed 04.01.2019
     * Added an if statement, which checks if the taxonomy already exists. Prevents errors, when the overall register is
     * called multiple times.
     */
    public function registerTaxonomies() {
        // The TAXONOMIES array contains the names, which the taxonomies are supposed to have as keys and the values
        // to those keys are the argument arrays for their registration within wordpress. So everything we have to do
        // is iterate this array and call register for each entry.
        foreach (self::TAXONOMIES as $taxonomy_name => $args) {

            if (!taxonomy_exists($taxonomy_name)) {
                register_taxonomy(
                    $taxonomy_name,
                    $this->post_type,
                    $args
                );
            }
        }
    }

    // *************************************
    // THE SAVING PROCESS FOR THIS POST TYPE
    // *************************************

    /**
     * This function gets hooked in, so it is called every time a new post is being saved into the database, but it
     * will only execute the relevant code, if the post is of the type "Event".
     *
     * CHANGELOG
     *
     * Added 04.01.2018
     *
     * @param string $post_id   The wordpress post id of the post that is being saved.
     * @return string
     */
    public function savePost(string $post_id) {
        if (!PostUtil::isSavingPostType('event', $post_id)) {
            return $post_id;
        }

        // This code part gets called when either
        // 1) The post is being saved, triggered by the used pressing the "Save" button within the admin edit panel
        // in this case the info is stored within the $_POST array
        // 2) The post is being saved programmatically by a script calling the "wp_insert_post" function
        return $post_id;
    }

}