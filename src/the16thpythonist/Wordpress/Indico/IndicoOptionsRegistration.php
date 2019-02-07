<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 30.01.19
 * Time: 11:00
 */

namespace the16thpythonist\Wordpress\Indico;


use the16thpythonist\Wordpress\Base\OptionPageRegistration;
use the16thpythonist\Wordpress\Base\PostRegistration;
use the16thpythonist\Wordpress\Functions\PostUtil;

/**
 * Class IndicoOptionsRegistration
 *
 * CHANGELOG
 *
 * Added 30.01.2019
 *
 * @package the16thpythonist\Wordpress\Indico
 */
class IndicoOptionsRegistration
{
    const PAGE_TITLE = 'IndicoWp settings';
    const MENU_TITLE = 'IndicoWp';
    const MENU_SLUG = 'indicowp';

    /**
     * Returns the menu slug for the options page
     *
     * CHANGELOG
     *
     * Added 30.01.2019
     *
     * @return string
     */
    public function getIdentifier()
    {
        return self::MENU_SLUG;
    }

    // **************************************
    // FUNCTIONS FOR REGISTERING IN WORDPRESS
    // **************************************

    /**
     * Registers all the necessary hooks for the settings page to appear in wordpress
     *
     * CHANGELOG
     *
     * Added 30.01.2019
     *
     */
    public function register() {
        add_action('admin_menu', array($this, 'addOptionPage'));
    }

    /**
     * Calls the necessary wordpress function to register a new options page and bind the callback for the html content
     * to it.
     * This method needs to be executed within the wordpress 'admin_menu' hook.
     *
     * CHANGELOG
     *
     * Added 30.01.2019
     */
    public function addOptionPage() {
        add_options_page(
            self::PAGE_TITLE,
            self::MENU_TITLE,
            'manage_options',
            self::MENU_SLUG,
            array($this, 'display')
        );
    }

    // **************************************
    // FOR DISPLAYING THE ACTUAL HTML CONTENT
    // **************************************

    /**
     * Returns the actual HTML to display the options page
     *
     * CHANGELOG
     *
     * Added 30.01.2019
     *
     * @return string
     */
    public function display() {
        // Here we are getting all the indico sites, that are currently saved in the wordpress system, to pass them
        // as a javascript object to the front end vue application code, that is being hooked in here.
        $sites = IndicoSitePost::getAllArrays();
        $javascript_object_name = 'INDICO_SITES';
        $javascript_code = PostUtil::javascriptExposeObjectArray($javascript_object_name, $sites);
        ?>
        <script>
            <?php echo $javascript_code; ?>
        </script>
        <div class="wrap">
            <div id="indico-options-main">
                <indico-options></indico-options>
            </div>
        </div>
        <?php
    }
}