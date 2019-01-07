<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 07.01.19
 * Time: 08:09
 */

namespace the16thpythonist\Wordpress\Indico;

use the16thpythonist\Indico\Event;

/**
 * Class EventAdapter
 *
 * This class will take an Event object returned by an IndicoApi and convert it into data formats needed by this
 * package for example directly deriving an arguments array for a insert/update operation from the data within the
 * event object
 *
 * CHANGELOG
 *
 * Added 07.01.2019
 *
 * @package the16thpythonist\Wordpress\Indico
 */
class EventAdapter
{
    public $event;

    /**
     * EventAdapter constructor.
     *
     * CHANGELOG
     *
     * Added 07.01.2019
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    // ***********************************************
    // METHODS FOR CREATING THE ALTERNATE DATA FORMATS
    // ***********************************************

    /**
     * This method will return the argument array needed to insert the event, which has been set for this adapter object
     *
     * CHANGELOG
     *
     * Added 07.01.2019
     *
     * @return array
     */
    public function getInsertArgs() {
        $args = array(
            'title'         => $this->event->getTitle(),
            'description'   => $this->event->getDescription(),
            'indico_id'     => $this->event->getID(),
            'starting'      => $this->event->getStartTime()->format('Y-m-d H:i:s'),
            'published'     => $this->event->getModificationTime()->format('Y-m-d H:i:s'),
            'type'          => $this->event->getType(),
            'creator'       => $this->event->getCreator()->getFullName(),
            'location'      => $this->event->getLocation(),
            'url'           => $this->event->getURL(),
        );
        return $args;
    }

}