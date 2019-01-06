<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 04.01.19
 * Time: 10:36
 */

namespace the16thpythonist\Wordpress\Indico;

/**
 * Class WpIndico
 *
 * CHANGELOG
 *
 * Added 04.01.2019
 *
 * @package the16thpythonist\Wordpress\Indico
 */
class WpIndico
{

    public static $REGISTRATION;

    // ****************************************
    // THE SETUP CODE FOR THE WP-INDICO PACKAGE
    // ****************************************

    /**
     * This method has to be called at the beginning of each plugin, it set up all the functionality of the package
     * so it can be used further down the line.
     *
     * CHANGELOG
     *
     * Added 04.01.2019
     *
     * Changed 06.01.2019
     * Removed all the actual registration code from this class and moved it to a separate registration class which
     * is now created and executed in this method.
     *
     * @param string $post_type The string post type
     * @param array $sites      An array specifying the indico sites to be observed
     */
    public static function register(string $post_type, array $sites) {

        self::$REGISTRATION = new WpIndicoRegistration($post_type);
        self::$REGISTRATION->register();

        // Adding the observed indico sites to the static container
        self::registerSites($sites);
    }

    /**
     * Given an array, that specifies the indico sites to be observed, this function adds them to the  KnownIndicoSites
     * Container
     *
     * CHANGELOG
     *
     * Added 06.01.2018
     *
     * @param array $sites
     */
    public static function registerSites(array $sites) {
        foreach ($sites as $site) {
            self::registerSite($site);
        }
    }

    /**
     * Given the arguments of a new indico site to be observed. This function will add it to the KnownIndicoSites
     * container:
     * The args array has to contain the following parameters:
     * - name: A unique string indentifier for the new site. This will be used in all future code to refer to the site.
     * - url: The URL to the main page of the indico site
     * - key: The personal api key to be used to request information
     * - categories: An array containing the category ids to be observed.
     *
     * CHANGELOG
     *
     * Added 06.01.2019
     *
     * @param array $args
     */
    public static function registerSite(array $args) {

        // Registering the site itself.
        KnownIndicoSites::addSite(
            $args['name'],
            $args['url'],
            $args['key']
        );

        // Adding all the categories to the site
        $categories = $args['categories'];
        foreach ($categories as $category_id) {
            KnownIndicoSites::addSiteCategory($args['name'], $category_id);
        }
    }

    // *************************************
    // GENERAL FUNCTIONALITY FOR THE PACKAGE
    // *************************************

    // ******************************************
    // GENERAL AJAX FUNCTIONALITY FOR THE PACKAGE
    // ******************************************


}