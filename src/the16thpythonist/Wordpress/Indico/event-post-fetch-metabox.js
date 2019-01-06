var fetch_button = jQuery('button#fetch-indico-event');

/**
 * Causes the server to fetch the indico event of the given indico ID from the given site and save it as the wordpress
 * post of the given post id
 *
 * CHANGELOG
 *
 * Added 05.01.2019
 *
 * @param site_name The name, identifying the indico site from which to get the event
 * @param indico_id The indico ID for the event to get
 * @param cb        The callback to be executed after successful ajax response. One parameter being the response string.
 */
function fetchEvent(site_name, indico_id, cb) {
    jQuery.ajax({
        url:        ajaxurl,
        type:       'GET',
        timeout:    1000,
        dataType:   'html',
        async:      true,
        data:       {
            action:     'fetch_indico_event',
            post_id:    post_id,
            indico_id:  indico_id,
            site:       site_name
        },
        success:    function (response) {
            console.log(response);
            cb(response);
        },
        error:      function (response) {
            console.log(response);
        }
    })
}

/**
 * Extracts the event info from the HTML of the edit page. Returns an object, which stores the indico ID in the
 * attribute 'indico_id' and the site name in the attribute 'site_name'
 *
 * CHANGELOG
 *
 * Added 05.01.2019
 */
function getEventInfo() {
    let indico_id = jQuery('select#indico-fetch-selection').attr('value');
    let site_name = jQuery('input#fetch-event-id').attr('value');
    return {
        indico_id: indico_id,
        site_name: site_name
    }
}

fetch_button.on('click', function () {
   // Reading the info, which the user has put in and then issuing to the server, that this event please be fetched
   // from the indico site's web API and put into this very post
   let indico_info = getEventInfo();

   fetchEvent(indico_info.site_name, indico_info.indico_id, function (response) {
       // Nothing really has to be done on success.
   });

   // We return true, because we obviously want a page reload to be able to see all the new info that has been updated
   // to the post
   return false;
});