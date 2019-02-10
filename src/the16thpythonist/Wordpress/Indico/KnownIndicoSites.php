<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 04.01.19
 * Time: 15:07
 */

namespace the16thpythonist\Wordpress\Indico;

/**
 * Class KnownIndicoSites
 *
 * Indico itself is just a software solution, that multiple people can set up and thus create an event management
 * website, specifically for their needs. This means there can be multiple indico sites and for each one a separate
 * api key is needed etc...
 * So there is also the possibility, that someone using this package might want to fetch events from different sites
 * and also from just a few specific categories within every website.
 *
 * This class is a static container, which will be populated at the beginning during the register method of the package
 *
 *
 * This could be reprogrammed to adhere to the singleton pattern.
 *
 * CHANGELOG
 *
 * Added 05.01.2019
 *
 * Deprecated 10.02.2019
 * This whole class was based on the idea of hardcoding the indico sites into the init code for the plugin, but now
 * they can be added as IndicoSitePost wordpress posts from the admin backend, making this system here obsolete.
 *
 * @deprecated
 *
 * @package the16thpythonist\Wordpress\Indico
 */
class KnownIndicoSites
{
    public static $INDICO_SITES = array();

    // ***************************
    // ADDING NEW SITE INFORMATION
    // ***************************

    /**
     * Adds a new indico site with the given url and api key under the given name.
     *
     * CHANGELOG
     *
     * Added 05.01.2019
     *
     * @deprecated
     *
     * @param string $name  A special name with which this site is to be identified
     * @param string $url   The url to the indico site
     * @param string $key   The api key, that can be used to request information from the site
     */
    public static function addSite(string $name, string $url, string $key) {
        self::$INDICO_SITES[$name] = array(
            'name'          => $name,
            'url'           => $url,
            'key'           => $key,
            'categories'    => array()
        );
    }

    /**
     * Adds a category id, from which the events should be fetched to wordpress, for a specific indico site, identified
     * by the given name
     *
     * CHANGELOG
     *
     * Added 05.01.2019
     *
     * @deprecated
     *
     * @param string $name
     * @param string $category_id
     */
    public static function addSiteCategory(string $name, string $category_id) {
        // Within the array given for the site identified by the given name, there is another array as the value to the
        // key 'categories' which is used to store all the category ids from which events are supposed to be fetched.
        self::$INDICO_SITES[$name]['categories'][] = $category_id;
    }

    // ***********************************
    // GETTING INFORMATION ABOUT THE SITES
    // ***********************************

    /**
     * Returns the array which contains all the info about the site, identified by the given name.
     * The array will contain the following keys:
     * - name
     * - url
     * - key
     * - categories
     *
     * CHANGELOG
     *
     * Added 05.01.2019
     *
     * @deprecated
     *
     * @param string $name  The name of the site
     * @return mixed
     */
    public static function getSite(string $name) {
        return self::$INDICO_SITES[$name];
    }

    /**
     * Returns an array with the string names of all the indico sites that have been registered in this class
     *
     * CHANGELOG
     *
     * Added 05.01.2019
     *
     * @deprecated
     *
     * @return array
     */
    public static function getAllSiteNames() {
        return array_keys(self::$INDICO_SITES);
    }

    /**
     * Returns an array, which contains arrays. Each of those arrays have the keys 'name', 'url', 'key' and
     * 'categories', specifying the indico sites, that have been registered with this class. the 'categories' key
     * has another array as value, which contains the category ids for all the interesting topics on the indico site.
     *
     * CHANGELOG
     *
     * Added 05.01.2019
     *
     * @deprecated
     *
     * @return array
     */
    public static function getAllSites() {
        return array_values(self::$INDICO_SITES);
    }

    /**
     * For a indico site identified by the given name, this method returns an array with all the category ids, that
     * are being observed on this specific site
     *
     * CHANGELOG
     *
     * Added 05.01.2019
     *
     * @deprecated
     *
     * @param string $name
     * @return mixed
     */
    public static function getSiteCategories(string $name) {
        return self::$INDICO_SITES[$name]['categories'];
    }
}