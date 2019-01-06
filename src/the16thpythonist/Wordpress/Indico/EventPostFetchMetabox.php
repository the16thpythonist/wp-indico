<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 04.01.19
 * Time: 10:40
 */

namespace the16thpythonist\Wordpress\Indico;

use the16thpythonist\Indico\IndicoApi;
use the16thpythonist\Wordpress\Base\Metabox;

/**
 * Class EventPostFetchMetabox
 *
 * CHANGELOG
 *
 * Added 04.01.2019
 *
 * @package the16thpythonist\Wordpress\Indico
 */
class EventPostFetchMetabox implements Metabox
{

    const ID = 'event-post-fetch-metabox';
    const TITLE = 'Request Event from Indico';

    // ***********************************
    // DISPLAYING THE HTML FOR THE METABOX
    // ***********************************

    /**
     * This method echos the whole html code, that makes up the display of the metabox.
     *
     * CHANGELOG
     *
     * Added 04.01.2019
     *
     * @param \WP_Post $post
     * @return mixed|void
     */
    public function display($post)
    {
        // An array containing arrays, which define the most important info about all the observed indico sites, from
        // which an event can possibly be fetched.
        $sites = KnownIndicoSites::getAllSites();
        ?>
        <div id="indico-fetch-event-container">
            <p>
                Do you want to load a new Event directly from Indico? <br>
                Then simply choose which indico site to use and simply import the event by its indico ID:
            </p>

            <select id="indico-fetch-selection" title="indico-fetch-selection">
                <?php foreach ($sites as $site): ?>
                    <option value="<?php echo $site['name']; ?>"><?php echo $site['url']; ?></option>
                <?php endforeach; ?>
            </select>

            <input type="text" id="fetch-event-id" name="fetch-event-id" value="paste indico event ID here">

            <button id="fetch-indico-event">
                Load Event!
            </button>
        </div>

        <script>
            // Here we need to pass the script the ID of the wordpress post on which this script is being executed,
            // because the script is later going to need it to send it with an AJAX request.
            var post_id = <?php global $post; echo $post->ID; ?>;

            // Dynamically loading the actual script to be executed with the metabox
            jQuery.getScript("<?php echo plugin_dir_url(__FILE__); ?>event-post-fetch-metabox.js", function () {
                //
            });
        </script>
        <?php
    }


    public function load($post)
    {
        // TODO: Implement load() method.
    }

    // *******************************
    // THE REGISTRATION OF THE METABOX
    // *******************************

    /**
     * Actually calls the necessary functions to hook in the other methods containing the registration code into the
     * wordpress system. Also registers Ajax callbacks.
     *
     * CHANGELOG
     *
     * Added 04.01.2019
     *
     * @return mixed|void
     */
    public function register()
    {
        // The metabox itself
        add_action('add_meta_boxes', array($this, 'registerMetabox'));

        // This is the callback for the AJAX request, that gets invoked, when actually sending off the info about
        // the event, that is supposed to be fetched.
        add_action('wp_ajax_fetch_indico_event', array($this, 'ajaxFetchIndicoEvent'));
    }

    /**
     * Registers the metabox to a callback and tells wordpress to associate it with the "Event" post type edit screen.
     *
     * CHANGELOG
     *
     * Added 04.01.2019
     */
    public function registerMetabox() {
        add_meta_box(
            self::ID,
            self::TITLE,
            array($this, 'display'),
            EventPost::$POST_TYPE,
            'normal',
            'high'
        );
    }

    // ****************************
    // SAVING DATA FROM THE METABOX
    // ****************************

    public function save($post_id)
    {
        // TODO: Implement save() method.
    }

    // **************************************
    // AJAX CALLBACKS REQUIRED BY THE METABOX
    // **************************************
    
    public function ajaxFetchIndicoEvent() {


        // Preventing an additional 0 to be appended to the response
        wp_die();
    }

    public function getIndicoEvent(string $url, string $key, string $indico_id) {
        $api = new IndicoApi($url, $key);
        $event = $api->getEvent($indico_id);

    }

}