<template>
    <!--
    26.02.2019
    Removed all the usage of VueBootstrap, because bootstrap is a real shit practice. Relying on "vanilla" html
    elements
    -->
    <div id="indico-sites-component">
        <h3>Observed indico sites</h3>
        <p>
            Add new <em>observed indico sites</em> here, or edit existing ones!<br>
            The IndicoWp plugin will be able to <em>automatically</em> import all the events from the indico sites you
            have specified here! The events will only be taken from the categories with the given category ids!
        </p>
        <div id="indico-sites-container" v-for="site in sites">

            <div class="indico-site-input" v-for="key in Object.keys(site)" v-if="key !== 'ID'">

                <p>{{ key }}:</p>
                <input type="text" v-model="site[key]">

            </div>

            <div>
                <button @click="updateSite(site)">update</button>
                <button @click="deleteSite(site)">delete</button>
            </div>

        </div>
    </div>
</template>

<style>
    #indico-sites-container {
        border-style: solid;
        border-radius: 5px;
        border-width: 1px;
        border-color: dimgrey;
        padding: 15px;
        display: flex;
        flex-direction: column;
        margin-bottom: 10px;
    }

    #indico-site-input-form .input-group-text {
        width: 100px;
    }

    .indico-site-input div {
        margin-bottom: 5px;
    }

</style>

<script>

    axios = require('axios');
    jquery = require('jquery');

    /**
     * This is a MODULE, that contains the functionality to actually issue the local changes to the server using AJAX
     * requests.
     *
     * This module offers the following functions:
     * - add(site):         Registers a new indico site post with the parameters of the given site
     * - update(site):      Updates the post corresponding to the id of the given site with the new parameters defined
     *                      by the given site
     * - remove(site):      Removes the site with the given name.
     *
     * CHANGELOG
     *
     * Added 10.02.2019
     *
     * @type {{add, update, remove}}
     */
    let indicoSiteAjax = (function () {

        /**
         * This function executes the given action on the wordpress server, passing along the given parameters from the
         * 'parameters' object.
         *
         * CHANGELOG
         *
         * Added 10.02.2019
         *
         * @param action_name
         * @param parameters
         */
        let doAction = function (action_name, parameters) {
            let data = {...{action: action_name}, ...parameters};
            jquery.ajax({
                url:        ajaxURL(),
                type:       'GET',
                timeout:    60000,
                dataType:   'html',
                async:      true,
                data:       data,
                success:    function (response) {
                    //console.log(response);
                },
                error:      function (response) {
                    //console.log(response);
                }
            });
        };

        /**
         * Creates a new wordpress indico site post with the parameters according to the given 'site' object
         *
         * CHANGELOG
         *
         * Added 10.02.2019
         *
         * @param site
         */
        let add = function (site) {
            doAction('add_indico_site', site);
        };

        /**
         * Updates the indico site object with the wordpress post id given as the 'ID' attribute of the passed site
         * object with the new values specified within site.
         *
         * CHANGELOG
         *
         * Added 10.02.2019
         *
         * @param site
         */
        let update = function (site) {
            doAction('update_indico_site', site);
        };

        /**
         * Removes the given site from the wordpress system
         *
         * CHANGELOG
         *
         * Added 10.02.2019
         *
         * @param site
         */
        let remove = function (site) {
            doAction('delete_indico_site', site)
        };

        return {
            add: add,
            update: update,
            remove: remove
        }
    })();

    /**
     * This is a MODULE, that contains the functionality to deal with 'site' objects.
     *
     * CHANGELOG
     *
     * Added 10.02.2019
     *
     * @type {{getEmpty, isEmpty, getValidity}}
     */
    let indicoSite = (function () {

        /**
         * Returns an empty site object, containing an empty string for the site name, url, key and categories
         * attribute.
         * Does not contain the 'ID' attribute! As only already existing sites can have a wordpress post id
         *
         * CHANGELOG
         *
         * Added 10.02.2019
         *
         * @returns {{name: string, key: string, url: string, categories: string}}
         */
        let getEmpty = function () {
            return {
                'name':         '',
                'key':          '',
                'url':          '',
                'categories':   ''
            };
        };

        /**
         * Whether or not the given site is empty.
         *
         * CHANGELOG
         *
         * Added 10.02.2019
         *
         * @param site
         *
         * @returns {boolean}
         */
        let isEmpty = function(site) {
            let isEmpty = true;
            Object.keys(site).forEach(function (key) {
                if (site[key] !== "") {
                    isEmpty = false;
                }
            });
            return isEmpty;
        };

        /**
         * Returns a 'validity' object, that contains the boolean value of whether or not the given site object
         * represents a valid input for a indico site or not. It also contains a message of what is wrong in case the
         * site object does not contain valid information.
         *
         * The list of all sites has to be passed to this function as well, as the site name needs to be unique and to
         * check that a reference to all other existing site names is necessary
         *
         * CHANGELOG
         *
         * Added 02.10.2019
         *
         * @param site
         * @param sites
         *
         * @returns {{status: boolean, message: string}}
         */
        let getValidity = function (site, sites) {
            let validity = {};

            // The site is only valid if the site name is unique and not already given to another site. Also it is only
            // valid if the site name is not an empty string!

            let counts = {};
            sites.forEach(x => counts[x.name] = (counts[x.name] || 0) + 1);
            console.log(counts);

            if (site.name === '') {
                validity.status = false;
                validity.message = 'Site must be named!'
            } else if (counts[site.name] > 1) {
                validity.status = false;
                validity.message = 'Site name is not unique!'
            } else {
                validity.status = true;
                validity.message = '';
            }

            return validity;
        };

        return {
            getEmpty: getEmpty,
            isEmpty: isEmpty,
            getValidity: getValidity
        }

    })();

    /**
     * Given the whole list of all sites currently being displayed, this will return the boolean value of whether or
     * not the list contains an empty site object (site object with all attributes being empty string)
     *
     * CHANGELOG
     *
     * Added 10.02.2019
     *
     * @param sites
     * @return {boolean}
     */
    function containsEmptySite(sites) {
        let containsEmpty = false;
        sites.forEach(function (site) {
            if (indicoSite.isEmpty(site)) {
                containsEmpty = true;
            }
        });
        return containsEmpty;
    }

    // Here the main vue functionality is defined
    module.exports = {
        data: function () {
            let sites = INDICO_SITES;
            console.log(sites);
            sites.push(indicoSite.getEmpty());
            return {
                sites:  sites
            }
        },
        methods: {

            /**
             * This is a callback, that gets called, when the "update" button for any of the sites is being pressed.
             * It needs to be passed the site object for the site of which the button was pressed.
             * This method will either add a new indico site post or update an existing one.
             *
             * CHANGELOG
             *
             * Added 10.02.2019
             *
             * @param site
             */
            updateSite: function (site) {

                // First we need to validate the information entered into the field.
                // The validity is an object, which contains the boolean "status" property, which is the actual flag
                // of whether or not it is valid, and the "message" property, which is a message that describes, what
                // is wrong in case it isn't valid.
                let validity = indicoSite.getValidity(site, this.sites);
                if (validity.status === false) {
                    alert(validity.message);
                } else {
                    // The normal update process (which includes sending the data to the server to be stored there) is
                    // only being executed if the entered site info is valid! (Thus being in this else branch)
                    if(site.hasOwnProperty('ID')) {
                        indicoSiteAjax.update(site);
                    } else {
                        indicoSiteAjax.add(site);
                    }

                    // If after updating the site, there are no empty sites in the list anymore, a new one will be added
                    // to it, so that the user can add more observed sites
                    if (!containsEmptySite(this.sites)) {
                        let emptySiteObject = indicoSite.getEmpty();
                        this.sites.push(emptySiteObject);
                    }
                }
            },

            /**
             * This is a callback, which gets called, when the "delete" button for any of the sites is being pressed.
             * It will delete that site.
             *
             * CHANGELOG
             *
             * Added 10.02.2019
             *
             * @param site
             */
            deleteSite: function (site) {

                // It is not possible to delete the empty entry at the end of the list, as that is used to enter
                // new sites for the user.
                if (site.name !== '') {

                    // finding the entry in the local sites array, with the matching name and then removing that from the
                    // array
                    for ( let i = 0; i < this.sites.length-1; i++) {
                        if ( this.sites[i].name  === site.name ) {
                            // The splice operation is used to remove objects at a certain index in javascript, by
                            // specifying the length of splice to 1 at the desired index.
                            this.sites.splice(i, 1);
                        }
                    }

                    // Actually sending to the server to remove the site
                    indicoSiteAjax.remove(site);
                }
            }
        }
    }
</script>