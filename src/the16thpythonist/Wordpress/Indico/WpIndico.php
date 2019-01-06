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
     * @param string $post_type The string post type
     */
    public static function register(string $post_type) {

        // Registering the "Event" post type
        EventPost::register($post_type);
    }

    // *************************************
    // GENERAL FUNCTIONALITY FOR THE PACKAGE
    // *************************************

    // ******************************************
    // GENERAL AJAX FUNCTIONALITY FOR THE PACKAGE
    // ******************************************


}