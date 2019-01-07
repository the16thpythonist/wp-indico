<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 06.01.19
 * Time: 14:51
 */

namespace the16thpythonist\Wordpress\Indico;

use the16thpythonist\Wordpress\WpCommands;
use Log\LogPost;
use the16thpythonist\Wordpress\Data\DataPost;

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

    // **************************
    // SETUP CODE FOR THE PACKAGE
    // **************************

    /**
     * Registers the post type "Event" in wordpress
     *
     * CHANGELOG
     *
     * Added 06.01.2019
     *
     * Changed 07.01.2019
     * Added the WpCommands registration and also the function to register all the commands implemented in this package
     *
     * @param bool $register_utilities
     */
    public function register($register_utilities=FALSE) {
        add_action('init', array($this, 'enqueueStylesheets'));

        // 07.01.2019
        // Activating the usage of the Wordpress Commands package and registering all the commands implemented by this
        // package
        if ($register_utilities) {
            LogPost::register('log');
            DataPost::register('data');
        }
        WpCommands::register();
        $this->registerCommands();

        // Registering the "Event" post type
        EventPost::register($this->post_type);

        $this->registerShortcodes();
    }

    /**
     * Registers all the commands, that were implemented for this package, so they can be used from within the sites
     * admin dashboard.
     *
     * CHANGELOG
     *
     * Added 07.01.2019
     */
    public function registerCommands() {
        FetchIndicoEventsCommand::register('fetch-new-events');
    }

    /**
     * Registers all the Shortcodes, that are implemented by this package.
     *
     * CHANGELOG
     *
     * Added 07.01.2019
     */
    public function registerShortcodes() {
        $upcoming_events_shortcode = new UpcomingEventsShortcode();
        $upcoming_events_shortcode->register();
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