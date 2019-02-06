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
        ?>
        <script>
            var INFO = "<?php echo count(KnownIndicoSites::$INDICO_SITES); ?>";
            var SITES = [
                <?php foreach (KnownIndicoSites::$INDICO_SITES as $site): ?>
                {
                    'name':         <?php echo $site['name']; ?>,
                    'key':          <?php echo $site['key']; ?>,
                    'url':          <?php echo $site['url']; ?>,
                    'categories':   <?php echo $site['categories']; ?>
                },
                <?php endforeach; ?>
            ];
        </script>
        <div class="wrap">
            <div id="indico-options-main">
                <indico-options></indico-options>
            </div>
        </div>
        <?php
    }
}