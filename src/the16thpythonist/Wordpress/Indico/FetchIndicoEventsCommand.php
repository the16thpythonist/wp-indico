<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 06.01.19
 * Time: 15:27
 */

namespace the16thpythonist\Wordpress\Indico;


use the16thpythonist\Command\Command;
use the16thpythonist\Indico\Event;
use the16thpythonist\Wordpress\Functions\PostUtil;

/**
 * Class FetchIndicoEventsCommand
 *
 * CHANGELOG
 *
 * Added 07.01.2019
 *
 * @package the16thpythonist\Wordpress\Indico
 */
class FetchIndicoEventsCommand extends Command
{

    public $params = array(

    );

    /**
     * The actual business code executed, when the command is issued.
     *
     * CHANGELOG
     *
     * Added 07.01.2019
     *
     * @param array $args
     * @return mixed|void
     */
    protected function run(array $args)
    {
        $args = array();
        $fetcher = new EventFetcher($args, $this->log);
        $this->log->info('CREATED THE FETCHER OBJECT');
        $events = $fetcher->getNew();

        // Inserting all the events into wordpress
        foreach ($events as $event) {
            $post_id = $this->insertEvent($event);
            // Logging which event has been posted and giving a direct link to that post
            $permalink = PostUtil::getPermalinkHTML($post_id);
            $this->log->info(sprintf('POSTED: "%s"', $permalink));
        }
    }

    /**
     * Given an Event object from the IndicoApi, this method will issue the right functions to insert it as a post
     * into wordpress.
     *
     * CHANGELOG
     *
     * Added 07.01.2019
     *
     * @param Event $event
     * @return int|\WP_Error
     */
    public function insertEvent(Event $event) {
        // This object creates the array with all the key value pairs needed for the insert method directly from the
        // attributes of the given Event object.
        $event_adapter = new EventAdapter($event);
        $args = $event_adapter->getInsertArgs();

        $post_id = EventPost::insert($args);
        return $post_id;
    }

}