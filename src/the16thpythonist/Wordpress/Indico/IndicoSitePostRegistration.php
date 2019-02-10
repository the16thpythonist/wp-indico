<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 04.02.19
 * Time: 17:15
 */

namespace the16thpythonist\Wordpress\Indico;

use the16thpythonist\Wordpress\Functions\PostUtil;

/**
 * Class IndicoSitePostRegistration
 *
 * CHANGELOG
 *
 * Added 04.02.2019
 *
 * @package the16thpythonist\Wordpress\Indico
 */
class IndicoSitePostRegistration
{
    public static $DUPLICATE_FLAG = FALSE;

    // ******************************
    // IMMUTABLE POST TYPE PROPERTIES
    // ******************************

    const LABEL = 'IndicoSite';
    const DESCRIPTION = 'This post type represents all the indico sites, that are being observed by the wp website.';

    // ******************
    // MUTABLE PROPERTIES
    // ******************

    public $post_type;

    /**
     * IndicoSitePostRegistration constructor.
     *
     * CHANGELOG
     *
     * Added 04.02.2019
     *
     * @param string $post_type
     */
    public function __construct(string $post_type)
    {
        $this->post_type = $post_type;
    }

    // ************************
    // METHODS FOR REGISTRATION
    // ************************

    /**
     * Hooks in all the relevant methods into the main wordpress system, so that the post type will be registered
     * correctly.
     *
     * CHANGELOG
     *
     * Added 04.02.2019
     */
    public function register() {

        // The register method should only get executed once. This is being assured by relying on a static flag, which
        // wll get changed after a successful registration, thus disabling the execution of another registration.
        if (!$this::$DUPLICATE_FLAG) {

            // Registering the actual post type in wordpress
            $this->registerPostType();
            // Exposing the ajax functions for this post type
            $this->registerAJAX();

            // Updating the duplicate flag, to prevent another registration
            $this::$DUPLICATE_FLAG = TRUE;
        }
    }

    /**
     * This method hooks in the function, which actually registers the post type with the appropriate configuration
     * arguments during the init step of wordpress.
     *
     * CHANGELOG
     *
     * Added 04.02.2019
     */
    public function registerPostType() {
        // Hooking the method which calls the actual "register_post_type" into the init action of wordpress
        add_action('init', array($this, 'configurePostType'));
    }

    /**
     * This method calls the wordpress function "register_post_type" with the appropriate argument array.
     * This method will need to be called within the init step of wordpress and thus needs to be hooked into the latter.
     *
     * CHANGELOG
     *
     * Added 04.02.2019
     */
    public function configurePostType() {
        // This is really only being called if there is not another post type by the same name already
        if(!post_type_exists($this->post_type)) {
            // This post type is supposed to be a source internal data representation only. These post objects are not
            // supposed to be editable by any user directly, instead they will be exposed through a custom widget for
            // editing/ deleting indico sites.
            $args = array(
                'label'                 => self::LABEL,
                'description'           => self::DESCRIPTION,
                'public'                => TRUE,
                'publicly_queryable'    => TRUE,
                'show_ui'               => TRUE,
            );
            register_post_type($this->post_type, $args);
        }
    }

    /**
     * Registers the ajax operations with wordpress, so that they can be accessed and used from the front end.
     *
     * CHANGELOG
     *
     * Added 04.02.2019
     */
    public function registerAJAX() {

        // Adding the option to insert an indico site
        add_action('wp_ajax_add_indico_site', array($this, 'ajaxInsertIndicoSite'));
        //add_action('wp_ajax_nopriv_insert_indico_site', array($this, 'ajaxInsertIndicoSite'));

        // Adding the option to update the values on an indico site
        add_action('wp_ajax_update_indico_site', array($this, 'ajaxUpdateIndicoSite'));
        //add_action('wp_ajax_nopriv_update_indico_site', array($this, 'ajaxUpdateIndicoSite'));

        // Adding the option to delete an indico site from the system
        add_action('wp_ajax_delete_indico_site', array($this, 'ajaxDeleteIndicoSite'));
        //add_action('wp_ajax_nopriv_delete_indico_site', array($this, 'ajaxDeleteIndicoSite'));
    }

    // *********************************
    // AJAX OPERATIONS FOR THE POST TYPE
    // *********************************

    /**
     * Inserts a new indico site post based on the values passed on through the _GET array.
     *
     * CHANGELOG
     *
     * Added 04.02.2019
     */
    public function ajaxInsertIndicoSite() {

        $expected_args = array('name', 'key', 'url', 'categories');
        if (PostUtil::containsGETParameters($expected_args)) {

            $args = self::insertArgsFromGet();
            IndicoSitePost::insert($args);
        }
        wp_die();
    }

    /**
     * Updates an indico post based in the values passed on through the _GET array.
     * This method expects to receive all the values of the fields. Even if they are not being changed, their old
     * value has to be sent anyways.
     *
     * CHANGELOG
     *
     * Added 04.02.2019
     */
    public function ajaxUpdateIndicoSite() {
        $expected_args = array('name', 'key', 'url', 'categories');
        if (PostUtil::containsGETParameters($expected_args)) {
            $args = self::insertArgsFromGet();
            $post_id = $_GET['ID'];
            IndicoSitePost::updatePost($post_id, $args);
        }
        wp_die();
    }

    /**
     * Deletes the indico site post identified by the site name passed on through the _GET array.
     *
     * CHANGELOG
     *
     * Added 04.02.2019
     */
    public function ajaxDeleteIndicoSite() {
        $expected_args = array('name');
        if (PostUtil::containsGETParameters($expected_args)) {

            $site_name = $_GET['name'];
            IndicoSitePost::deleteSite($site_name);
        }
        wp_die();
    }

    /**
     * Returns an array with arguments for inserting a new indico site post, which were extracted from the _GET array,
     * using the default key names.
     *
     * CHANGELOG
     *
     * Added 04.02.2019
     *
     * @return array
     */
    protected static function insertArgsFromGet() {
        $insert_args = array(
            'name'          => $_GET['name'],
            'key'           => $_GET['key'],
            'url'           => $_GET['url'],
            'categories'    => str_getcsv($_GET['categories'])
        );
        return $insert_args;
    }

    /**
     * Returns an array with arguments for inserting a new indico site post, which were extracted from the _POST array,
     * using the default key names.
     * This method is supposed to be called during the handling of an AJAX request.
     *
     * CHANGELOG
     *
     * Added 10.02.2019
     *
     * @return array
     */
    protected static function insertArgsFromPost() {
        $insert_args = array(
            'name'          => $_POST['name'],
            'key'           => $_POST['key'],
            'url'           => $_POST['url'],
            'categories'    => $_POST['categories']
        );
        return $insert_args;
    }
}