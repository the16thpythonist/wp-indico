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
use the16thpythonist\Wordpress\Functions\PostUtil;

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
    public $register_utility_flag;

    /**
     * WpIndicoRegistration constructor.
     *
     * CHANGELOG
     *
     * Added 06.01.2019
     *
     * @param string $post_type
     */
    public function __construct(string $post_type, bool $register_utility=FALSE)
    {
        $this->post_type = $post_type;
        $this->register_utility_flag = $register_utility;
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
     * Changed 30.01.2019
     * Put all the registrations into separate methods and then just calling these methods now.
     * Added an additional method for registering the options page
     *
     * Changed 31.01.2019
     * Also removed the direct call bind enqueueStylesheets to a action hook. Instead using the register assets method
     * now, which will handle stylesheets and the scripts.
     */
    public function register() {

        $this->registerAssets();
        $this->registerUtility();
        $this->registerPages();
        $this->registerCommands();
        $this->registerPostTypes();
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

    /**
     * Registers all the post types relevant to the indico plugin.
     * Currently only includes the "event" post type.
     *
     * CHANGELOG
     *
     * Added 30.01.2019
     *
     * Changed 04.02.2019
     * Added the "IndicoSitePost" post type to also be registered.
     */
    public function registerPostTypes() {
        EventPost::register($this->post_type);

        // 04.02.2019
        // The "indico site" post type will be used as code-internal data representation of observed indico sites.
        // it is not publicly exposed or editable.
        IndicoSitePost::register('indico-sites');
    }

    /**
     * Registers additional static pages within the wordpress admin area.
     * Currently only includes the custom options page.
     */
    public function registerPages() {
        $option_page_registration = new IndicoOptionsRegistration();
        $option_page_registration->register();
    }

    /**
     * Registers all the additional packages, on which the functionality of this package is based on.
     * That would be the LogPost and the DataPost as well as the WpCommands package.
     */
    public function registerUtility() {
        if ($this->register_utility_flag) {
            LogPost::register('log');
            DataPost::register('data');
        }
        WpCommands::register();
    }

    /**
     * Calls the functions to hook in the enqueue of all the relevant stylesheets and scripts
     *
     * CHANGELOG
     *
     * Added 31.01.2019
     */
    public function registerAssets() {
        // The CSS stylesheets
        add_action('init', array($this, 'enqueueStylesheets'));

        // The JS scripts
        add_action('init', array($this, 'enqueueScripts'));
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

        wp_enqueue_style(
            'boostrap',
            '//unpkg.com/bootstrap/dist/css/bootstrap.min.css'
        );

        wp_enqueue_style(
            'bootstrap-vue',
            '//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.css'
        );
    }

    // ***********************
    // SCRIPTS FOR THE PACKAGE
    // ***********************

    /**
     * Calls the functions to add the Vue.js framework to the header of each HTML document
     *
     * CHANGELOG
     *
     * Added 31.01.2019
     */
    public function enqueueScripts() {
        /**wp_enqueue_script(
            'vue',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.16/vue.js',
            [],
            '2.5.16'
        );**/

        wp_enqueue_script(
            'build',
            plugin_dir_url(__FILE__) . 'build.js',
            [],
            '0.0.2',
            true
        );
    }

    // **************************
    // GENERAL AJAX FUNCTIONALITY
    // **************************
}