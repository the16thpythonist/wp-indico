# IndicoWp


## CHANGELOG

### 0.0.0.0 - 04.01.2019

- initial version

### 0.0.0.1 - 04.01.2019

- Added "EventPost" class, which is a wrapper object around posts of the custom type "Event"
- Added "EventPostRegistration" class which manages the registration of the post type "Event" 
to the wordpress system.

### 0.0.0.2 

- Added the "WpIndico" class, which acts as a facade for the whole package functionality
- Added "EventPostFetchMetabox", which manages the registration and functionality of a metabox
within the "event" post type edit screen, with which a event can be directly created by fetching 
it from an indico site directly.
- Added "event-post-fetch-metabox.js" Script, which will be loaded and used by the fetch metabox