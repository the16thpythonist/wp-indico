<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 06.01.19
 * Time: 15:28
 */

namespace the16thpythonist\Wordpress\Indico;


use Log\LogInterface;
use Log\LogPost;
use the16thpythonist\Indico\Event;
use the16thpythonist\Indico\IndicoApi;

/**
 * Class EventFetcher
 *
 * CHANGELOG
 *
 * Added 06.01.2019
 *
 * @package the16thpythonist\Wordpress\Indico
 */
class EventFetcher
{
    public $args;

    public $api;

    public $log;

    const DEFAULT_ARGS = array(

    );

    /**
     * EventFetcher constructor.
     *
     * CHANGELOG
     *
     * Added 06.01.2019
     *
     * @param array $args
     * @param $log
     */
    public function __construct(array $args, LogInterface $log)
    {
        $this->args = array_replace(self::DEFAULT_ARGS, $args);

        // We loop through each observed site and fetch the events separately
        $this->log = $log;
    }

    /**
     * This method returns an array with all the Event objects from the IndicoApi, originating only from the observed
     * categories of the observed sites and which are NOT already represented as posts within the wordpress system.
     *
     * CHANGELOG
     *
     * Added 06.01.2019
     *
     * @return array
     */
    public function getNew() {
        // We loop through each observed site and fetch the events separately
        $events = array();
        $sites = KnownIndicoSites::getAllSites();
        $this->log->info('NUMBER OF OBSERVED INDICO SITES: ' . count($sites));
        foreach ($sites as $site) {
            $this->log->info(sprintf('STARTING TO FETCH FOR: <a href="%s">%s</a>', $site['url'], $site['name']));
            $site_events = $this->getNewSiteEvents($site);
            $events = array_merge($events, $site_events);
        }
        return $events;
    }

    /**
     * Given the array which describes an observed indico site (name, url, key and categories array) This method will
     * return all the Event objects from the IndicoApi, which are not already represented as posts on the wordpress
     * system.
     *
     * CHANGELOG
     *
     * Added 06.01.2019
     *
     * @param array $site
     * @return array
     */
    public function getNewSiteEvents(array $site) {
        // Creating a new API object for the specific site
        $this->api = new IndicoApi($site['url'], $site['key']);
        $this->log->info('CREATED A NEW INDICO API OBJECT FOR THE SITE');

        // Computing all the indico IDs of the events that are already on the wordpress system as posts (those obv.
        // dont have to be posted again)
        $event_posts = $this->getEventPostsOfSite($site['name']);
        $indico_ids = array_map(function ($e) {return $e->indico_id;}, $event_posts);
        $this->log->info('NUMBER OF POSTS BELONGING TO THIS SITE: ' . count($indico_ids));

        $site_events = array();
        // Iterating through all the categories
        foreach ($site['categories'] as $category_id) {
            $events = $this->api->getCategory($category_id);
            $this->log->info(sprintf('NUMBER OF EVENTS FOR CATEGORY "%s": %s', $category_id, count($events)));
            $filtered_events = $this->getFilteredEvents($events, $indico_ids);
            $site_events = array_merge($site_events, $filtered_events);
        }
        return $site_events;
    }

    /**
     * Given an array of Event objects from the IndicoApi and an array of indico IDs, this function will return an
     * array with all those Events of the given array, whose indico IDs do NOT appear in the given ID list. Thus this
     * excludes all events with ids in the given excluded_indico_ids array
     *
     * CHANGELOG
     *
     * Added 06.01.2019
     *
     * @param array $events
     * @param array $excluded_indico_ids
     * @return array
     */
    public function getFilteredEvents(array $events, array $excluded_indico_ids) {
        $filtered_events = array();
        /** @var Event $event */
        foreach ($events as $event) {
            $indico_id = $event->getID();
            if (!in_array($indico_id, $excluded_indico_ids)) {
                $filtered_events[] = $event;
            }
        }
        return $filtered_events;
    }

    /**
     * Returns an array of all EventPost wrappers for all the events, that are represented as wordpress posts on the
     * system and originate from the indico site, identified by the given site name.
     *
     * CHANGELOG
     *
     * Added 06.01.2019
     *
     * @param string $site_name
     * @return array
     */
    public function getEventPostsOfSite(string $site_name) {
        // We will identify the site from which an Event originates by the site url meta value. We will compare that
        // url, which is an attribute of the event post with the URL of the given site
        $site = KnownIndicoSites::getSite($site_name);

        $args = array(
            'post_type'         => EventPost::$POST_TYPE,
            'post_status'       => 'publish',
            'posts_per_page'    => -1,
            'meta_query'        => array(
                array(
                    'key'           => 'site_url',
                    'value'         => $site['url'],
                    'compare'       => 'IN'
                )
            )
        );
        $query = new \WP_Query($args);
        $posts = $query->get_posts();

        // Creating EventPost wrappers around those post objects
        $event_posts = array();
        foreach ($posts as $post) {
            $event_posts[] = new EventPost($post->ID);
        }
        return $event_posts;
    }
}