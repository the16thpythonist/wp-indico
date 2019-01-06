<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 06.01.19
 * Time: 14:51
 */

namespace the16thpythonist\Wordpress\Indico;

/**
 * Class WpIndicoRegistration
 *
 * CHANGELOG
 *
 * Added 06.01.2019
 *
 * @package the16thpythonist\Wordpress\Indico
 */
class WpIndicoRegistration
{

    public $post_type;

    /**
     * WpIndicoRegistration constructor.
     *
     * CHANGELOG
     *
     * Added 06.01.2019
     *
     * @param string $post_type
     */
    public function __construct(string $post_type)
    {
        $this->post_type = $post_type;
    }

    /**
     * Registers the post type "Event" in wordpress
     *
     * CHANGELOG
     *
     * Added 06.01.2019
     */
    public function register() {
        add_action('init', array($this, 'enqueueStylesheets'));

        // Registering the "Event" post type
        EventPost::register($this->post_type);
    }

    // ***************************
    // STYLESHEETS FOR THE PACKAGE
    // ***************************

    /**
     * Calls the functions to add the indico specific stylesheet to the queue for appending the links to the
     * document header
     *
     * CHANGELOG
     *
     * Added 06.01.2019
     */
    public function enqueueStylesheets() {
        wp_enqueue_style(
            'indico-style',
            plugin_dir_url(__FILE__) . 'wp-indico.css'
        );
    }

    // ***********************
    // SCRIPTS FOR THE PACKAGE
    // ***********************

    // **************************
    // GENERAL AJAX FUNCTIONALITY
    // **************************
}