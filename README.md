# IndicoWp


## CHANGELOG

### 0.0.0.0 - 04.01.2019

- initial version

### 0.0.0.1 - 04.01.2019

- Added "EventPost" class, which is a wrapper object around posts of the custom type "Event"
- Added "EventPostRegistration" class which manages the registration of the post type "Event" 
to the wordpress system.

### 0.0.0.2 - 06.01.2019

- Added the "WpIndico" class, which acts as a facade for the whole package functionality
- Added "EventPostFetchMetabox", which manages the registration and functionality of a metabox
within the "event" post type edit screen, with which a event can be directly created by fetching 
it from an indico site directly.
- Added "event-post-fetch-metabox.js" Script, which will be loaded and used by the fetch metabox
- Added some simple test cases for utility functions
- Added the "KnownIndicoSites" class, which acts as a container and access point for the info about 
all observed indico sites and their urls, api keys etc.
- Added "WpIndicoRegistration" which handles the registration operations for the whole packages such as enqueueing the 
stylesheets and scripts used.
- Added "wp-indico.css"
    - Added styles for the fetch metabox of the event type

### 0.0.0.3 - 07.01.2019

- Added "EventFetcher", which handles the actual requests to the web APIs of the observed indico sites
- Added the "FetchIndicoEventsCommand" command, with which new Events can be fetched from all the observed indico sites
- Added EventAdapter. A simple utility class, which directly creates an arguments array for the post insert operation 
from the Event objects returned by the IndicoApi
- Added the project [wp-commands](https://github.com/the16thpythonist/wp_commands) to the project for handling parallel 
background commands.
- Added the 'display-upcoming-events' shortcode, with which a list of the next events can be generated.

### 0.0.0.4 - 05.02.2019

- Using Vue.JS as front end library now.
- Added the new hidden post type "indico site", which stores the info about all the observed indico sites.
