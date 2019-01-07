<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 07.01.19
 * Time: 10:36
 */

namespace the16thpythonist\Wordpress\Indico;


use the16thpythonist\Wordpress\Base\Shortcode;

/**
 * Class UpcomingEventsShortcode
 *
 * CHANGELOG
 *
 * Added 07.01.2019
 *
 * @package the16thpythonist\Wordpress\Indico
 */
class UpcomingEventsShortcode
{
    const NAME = 'display-upcoming-events';

    const DEFAULT_ARGS = array(
        'class'             => 'upcoming-events',
        'type'              => 'div',
        'format'            => 'short',
        'count'             => 5
    );

    /**
     * Returns the name of the Shortcode.
     *
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    // **************************************
    // REGISTERING THE SHORTCODE IN WORDPRESS
    // **************************************

    /**
     * Registers the shortcode with wordpress, so it knows which callback to invoke, when parses this shortcode
     *
     * CHANGELOG
     *
     * Added 07.01.2019
     */
    public function register()
    {
        add_shortcode(self::NAME, array($this, 'display'));
    }

    // ************************************
    // METHODS FOR DISPLAYING THE HTML CODE
    // ************************************

    /**
     * Returns the whole HTML code for the shortcode based on the arguments given to the shortcode.
     *
     * CHANGELOG
     *
     * Added 07.01.2019
     *
     * @param array $args
     * @return string
     */
    public function display(array $args)
    {
        $args = array_replace(self::DEFAULT_ARGS, $args);
        // Getting a list of all the EventPost objects
        $event_posts = EventPost::getAll();

        // Sorting the list of EventPosts by the time at which the start.
        usort($event_posts, array($this, 'compareEventStartTimes'));

        ob_start();
        ?>
            <div class="<?php  echo $args['class']; ?>">
                <?php $this->displayListing($event_posts, $args)?>
            </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Echos the HTML code for the actual listing. Based on the args it can be short or long items and either be a list
     * made of div containers or li bullet points
     *
     * CHANGELOG
     *
     * Added 07.01.2019
     *
     * @param array $event_posts
     * @param $args
     */
    public function displayListing(array $event_posts, $args) {
        $count = 0;
        $tag = ($args['type'] === 'li' ? 'ul' : 'div');
        ?>
            <<?php echo $tag; ?>>
                <?php
                    foreach ($event_posts as $event_post) {
                        if ($count >= $args['count']) {break; }

                        $this->displayItem($event_post, $args['type'], $args['format']);
                        $count++;
                    }
                ?>
            </<?php echo $tag; ?>>
        <?php
    }

    /**
     * Echos the html code for a single item based on the given event post. The format can be either long with a
     * description or short without depending on the format parameter. The type defines if the singular items are
     * bullet points (li) or just wrapped in separate containers (div).
     *
     * CHANGELOG
     *
     * Added 07.01.2019
     *
     * @param EventPost $event_post
     * @param string $type
     * @param string $format
     */
    public function displayItem(EventPost $event_post, string $type, string $format) {
        // First we create the actual tags, that enclose the item (li or div) based on the given type
        ?>
            <<?php echo $type;?> class="listing-item">
                <?php
                    if($format === 'short') {
                        $this->displayItemContentShort($event_post);
                    } elseif ($format === 'long') {
                        $this->displayItemContentLong($event_post);
                    }
                ?>
            </<?php echo $type;?>>
        <?php
    }

    /**
     * This function echos the HTML code, which displays the long version of the item content, which is the title, that
     * links to the post page, followed by the location and the starting date. It also contains a 300 word excerpt of
     * the event description.
     *
     * CHANGELOG
     *
     * Added 07.01.2019
     *
     * @param EventPost $event_post
     */
    public function displayItemContentLong(EventPost $event_post) {

        // Calculating a little excerpt of the description
        $sanitized_description = sanitize_textarea_field($event_post->description);
        $excerpt = substr($sanitized_description, 0, 300) . '...';
       ?>
            <a href="<?php echo get_the_permalink($event_post->ID); ?>">
                <?php echo $event_post->title; ?>
            </a>
            <span> - </span>
            <span>
                <?php echo sprintf('%s, %s. %s', $event_post->start, $event_post->location, $excerpt);?>
            </span>
        <?php
    }

    /**
     * This function echos the HTML code, which displays the short version of the item content, which just consists
     * of the name, which links to the specific post and the starting date int brackets right after.
     *
     * CHANGELOG
     *
     * Added 07.01.2019
     *
     * @param EventPost $event_post
     */
    public function displayItemContentShort(EventPost $event_post) {
        ?>
            <a href="<?php echo get_the_permalink($event_post->ID); ?>">
                <?php echo $event_post->title; ?>
            </a>
            <?php echo sprintf('(%s)', $event_post->start) ?>
        <?php
    }

    /**
     * Returns a positive value if the first event starts later and a negative value if the second event starts later.
     *
     * This method will be used to sort the EventPost objects by the time they start.
     *
     * CHANGELOG
     *
     * Added 07.01.2019
     *
     * @param EventPost $event1
     * @param EventPost $event2
     * @return false|int
     */
    public function compareEventStartTimes(EventPost $event1, EventPost $event2) {
        $time_difference = strtotime($event1->start) - strtotime($event2->start);
        return $time_difference;
    }

}