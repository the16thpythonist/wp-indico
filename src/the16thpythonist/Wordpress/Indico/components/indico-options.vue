<template>
    <div id="indico-sites-component">
        <h3>Observed indico sites</h3>
        <p>
            Add new <em>observed indico sites</em> here, or edit existing ones!<br>
            The IndicoWp plugin will be able to <em>automatically</em> import all the events from the indico sites you
            have specified here! The events will only be taken from the categories with the given category ids!
        </p>
        <div id="indico-sites-container" v-for="site in sites">

            <div class="indico-site-input" v-for="key in Object.keys(site)">

                <b-input-group id="indico-site-input-form" :prepend="key" size="sm">
                    <b-form-input type="text" v-model="site[key]"></b-form-input>
                </b-input-group>

            </div>

            <b-button-group id="indico-site-buttons">
                <b-button variant="success" @click="updateSite(site)" size="sm">update</b-button>
                <b-button variant="danger" @click="deleteSite(site)" size="sm">delete</b-button>
            </b-button-group>

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

    let axios = require('axios');

    function emptySite() {
        return {
            'name':         '',
            'key':          '',
            'url':          '',
            'categories':   ''
        };
    }

    function isSiteEmpty(site) {
        let isEmpty = true;
        Object.keys(site).forEach(function (key) {
            if (site[key] !== "") {
                isEmpty = false;
            }
        });
        return isEmpty;
    }

    function containsEmptySite(sites) {
        let containsEmpty = false;
        sites.forEach(function (site) {
            if (isSiteEmpty(site)) {
                containsEmpty = true;
            }
        });
        return containsEmpty;
    }

    function getSiteValidity(site, sites) {
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
    }

    console.log(SITES);
    console.log(INFO);

    module.exports = {
        props: {
            sites: {
                default: function() {
                    let sites = SITES;
                    sites.push(emptySite());
                    return sites;
                }
            }
        },
        methods: {
            updateSite: function (site) {

                // First we need to validate the information entered into the field.
                // The validity is an object, which contains the boolean "status" property, which is the actual flag
                // of whether or not it is valid, and the "message" property, which is a message that describes, what
                // is wrong in case it isn't valid.
                let validity = getSiteValidity(site, this.sites);
                if (validity.status === false) {
                    alert(validity.message);
                } else {
                    // The normal update process (which includes sending the data to the server to be stored there) is
                    // only being executed if the entered site info is valid! (Thus being in this else branch)

                    // Sending the data to the server
                    /**
                    console.log(ajaxURL());
                    axios({
                        method:     'get',
                        url:        ajaxURL(),
                        data:       {
                            action:     'add_indico_site',
                            name:       site.name,
                            url:        site.url,
                            key:        site.key,
                            categories: site.categories
                        }
                    }).then(function (response) {
                        console.log(response);
                    }).catch(function (error) {
                        console.log(error);
                    });
                    **/

                    jQuery.ajax({
                        url:        ajaxURL(),
                        type:       'GET',
                        timeout:    60000,
                        dataType:   'html',
                        async:      true,
                        data:       {
                            action:     'add_indico_site',
                            name:       site.name,
                            url:        site.url,
                            key:        site.key,
                            categories: site.categories
                        },
                        success:    function (response) {
                            console.log(response);
                        },
                        error:      function (response) {
                            console.log(response);
                        }
                    });


                    // If after updating the site, there are no empty sites in the list anymore, a new one will be added
                    // to it, so that the user can add more observed sites
                    if (!containsEmptySite(this.sites)) {
                        let emptySiteObject = emptySite();
                        this.sites.push(emptySiteObject);
                    }
                }
            },
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
                }
            }
        }
    }
</script>