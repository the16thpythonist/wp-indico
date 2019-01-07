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
use the16thpythonist\Indico\Event;
use the16thpythonist\Wordpress\Functions\PostUtil;

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
     * Changed 06.01.2019
     * Added an additional global variable to the javascript, which contains the url to the edit page of exactly this
     * post. It is being called after the new event has been successfully fetched and inserted to redirect to the edit
     * page of that event.
     *
     * @param \WP_Post $post
     * @return mixed|void
     */
    public function display($post)
    {
        // An array containing arrays, which define the most important info about all the observed indico sites, from
        // which an event can possibly be fetched.
        $sites = KnownIndicoSites::getAllSites();
        global $post;
        ?>
        <div id="indico-fetch-event-container">
            <p>
                Do you want to load a new Event <strong>directly from Indico?</strong> <br>
                Then simply <em>choose which indico site</em> to use and simply import the event by its <em>indico ID</em>:
            </p>

            <select id="indico-fetch-selection" title="indico-fetch-selection">
                <?php foreach ($sites as $site): ?>
                    <option value="<?php echo $site['name']; ?>"><?php echo $site['url']; ?></option>
                <?php endforeach; ?>
            </select>

            <input type="text" id="fetch-event-id" name="fetch-event-id" placeholder="paste indico event ID here">

            <button id="fetch-indico-event" class="material-button-indico">
                Load!
            </button>
        </div>

        <script>
            // Here we need to pass the script the ID of the wordpress post on which this script is being executed,
            // because the script is later going to need it to send it with an AJAX request.
            var post_id = <?php echo $post->ID; ?>;
            var post_url = "<?php echo get_site_url() . '/wp-admin/post.php?post=' . $post->ID . "&action=edit"; ?>";

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

    /**
     * AJAX callback. Will get the event according to the specified indico id and site and then update the given post
     * with that info
     *
     * CHANGELOG
     *
     * Added 06.01.2019
     */
    public function ajaxFetchIndicoEvent() {
        $params = array('post_id', 'indico_id', 'site');

        if (!PostUtil::containsGETParameters($params)) {
            wp_die();
        }

        $post_id = $_GET['post_id'];
        $indico_id = $_GET['indico_id'];
        $site = KnownIndicoSites::getSite($_GET['site']);

        // Getting the event from the actual Indico site. This involves network requests
        try {
            $event = $this->getIndicoEvent($site['url'], $site['key'], $indico_id);
            $this->updateEvent($post_id, $event);
        } catch (\Error $e) {
            // echo var_export($event->data, TRUE);
            echo $e->getMessage();
        }


        // Preventing an additional 0 to be appended to the response
        wp_die();
    }

    /**
     * Will use the IndicoApi to request the event with the given indico ID from the indico site defined by the url,
     * using the given api key
     *
     * CHANGELOG
     *
     * Added 06.01.2019
     *
     * @param string $url
     * @param string $key
     * @param string $indico_id
     * @return Event
     */
    public function getIndicoEvent(string $url, string $key, string $indico_id) {
        $api = new IndicoApi($url, $key);
        $event = $api->getEvent($indico_id);

        return $event;
    }

    /**
     * Given the Event response object from the IndicoApi object and the wordpress post id it will update this post
     * with the info from the given event.
     *
     * CHANGELOG
     *
     * Added 06.01.2019
     *
     * Changed 07.01.2019
     * Replaced the manual creation of the arguments array with the usage of a specific adapter object, which creates
     * the arguments array from the event object.
     *
     * @param string $post_id
     * @param Event $event
     */
    public function updateEvent(string $post_id, Event $event) {
        // 07.01.2019
        // Creating the arguments array for the insert operation using an adapter object, which does just that
        $event_adapter = new EventAdapter($event);
        $args = $event_adapter->getInsertArgs();

        EventPost::update($post_id, $args);
    }

}