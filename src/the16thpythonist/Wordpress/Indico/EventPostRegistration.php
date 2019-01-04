<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 04.01.19
 * Time: 09:33
 */

namespace the16thpythonist\Wordpress\Indico;

use the16thpythonist\Wordpress\Base\PostRegistration;

class EventPostRegistration implements PostRegistration
{
    public $post_type;

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

    public function register() {
        // Registering the post type first
        add_action('init', array($this, 'registerPostType'));

        // All the taxonomies
        add_action('init', array($this, 'registerTaxonomies'));
    }

    /**
     * CHANGELOG
     *
     * Added 04.01.2019
     */
    public function registerPostType() {
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

    /**
     * Registers the custom taxonomies for the creator, type and location of the event in wordpress
     *
     * CHANGELOG
     *
     * Added 04.01.2019
     */
    public function registerTaxonomies() {
        // The TAXONOMIES array contains the names, which the taxonomies are supposed to have as keys and the values
        // to those keys are the argument arrays for their registration within wordpress. So everything we have to do
        // is iterate this array and call register for each entry.
        foreach (self::TAXONOMIES as $taxonomy_name => $args) {
            register_taxonomy(
                $taxonomy_name,
                $this->post_type,
                $args
            );
        }
    }

}