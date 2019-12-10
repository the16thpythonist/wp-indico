<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 04.01.19
 * Time: 09:33
 */

namespace the16thpythonist\Wordpress\Indico;

use the16thpythonist\Wordpress\Base\PostRegistration;
use the16thpythonist\Wordpress\Functions\PostUtil;

/**
 * Class EventPostRegistration
 *
 * CHANGELOG
 *
 * Added 04.01.2019
 *
 * @package the16thpythonist\Wordpress\Indico
 */
class EventPostRegistration
{
    public $post_type;

    public $meta_boxes = array();

    const LABEL = 'Event';
    const ICON = 'dashicons-groups';
    const TAXONOMIES = array(
        'creator'   => array(
            'label'     => 'Creator',
            'public'    => true
        ),
        'type'      => array(
            'label'     => 'Type',
            'public'    => true,
        ),
        'location'  => array(
            'label'     => 'Location',
            'public'    => true
        )
    );

    /**
     * EventPostRegistration constructor.
     *
     * CHANGELOG
     *
     * Added 04.01.2019
     *
     * @param $post_type
     */
    public function __construct($post_type)
    {
        // The name for the post type has to be specified as a parameter. It will not be hardcoded. If it has to be
        // used it can be accessed as a static attribute of the facade for this package.
        $this->post_type = $post_type;
    }

    /**
     * Returns the exact string name, under which the "event" post type is registered within wordpress.
     *
     * @return mixed
     */
    public function getPostType()
    {
        return $this->post_type;
    }

    // *********************************
    // THE REGISTRATION OF THE POST TYPE
    // *********************************

    /**
     * Actually hooks in the functions, which do the registration into the init hook
     *
     * CHANGELOG
     *
     * Added 04.01.2019
     */
    public function register() {
        // Registering the post type first
        add_action('init', array($this, 'registerPostType'));

        // All the taxonomies
        add_action('init', array($this, 'registerTaxonomies'));

        add_action('save_post', array($this, 'savePost'));

        // The metaboxes are registered by creating their own Metabox objects, which handle the registration, because
        // that is a lengthy process and none of the the concern of this class.
        // Thus this method does not need to be hooked in to anything, but rather executed right away.
        $this->registerMetaboxes();

        // 10.12.2019
        // This method wraps all the hook callback registrations to modify this post types list view in the admin area
        $this->registerAdminListViewModification();
    }

    /**
     * Wraps the callback registrations for all the hooks, that are involved in modifying the list view of this post
     * type  within the admin area of the site
     *
     * CHANGELOG
     *
     * Added 10.12.2019
     */
    public function registerAdminListViewModification(){

        // This filter will be used to define, which columns the list view is supposed to have
        add_filter(
            $this->insertPostType('manage_%s_posts_columns'),
            array($this, 'manageEventColumns'),
            10, 1
        );

        // This action will be used to generate the actual contents for the columns
        add_action(
            $this->insertPostType('manage_%s_posts_custom_column'),
            array($this, 'contentEventColumns'),
            10, 2
        );

    }

    /**
     * This function takes a string, which has to contain exactly one string position for inserting a
     * string with the "sprintf" function.
     * This position will be inserted with the post type string of this class.
     * This function will be needed in situations, where the name of a hook is dynamically dependant on the post type
     * for example.
     *
     * EXAMPLE
     *
     * $this->post_type = "author"
     * $this->insertPostType("manage_%s_posts")
     * >> "manage_author_posts"
     *
     * CHANGELOG
     *
     * Added 10.12.2019
     *
     * @param string $template
     * @return string
     */
    public function insertPostType(string $template) {
        return sprintf($template, $this->post_type);
    }

    /**
     * Registers all the meta boxes, that are needed for the "Event" post type. The metaboxes will appear in the
     * "edit post" screen for a "Event" post.
     *
     * CHANGELOG
     *
     * Added 04.01.2019
     *
     * Changed 26.10.2019
     * Removed the registration of the fetch meta box as it is no longer being supported.
     */
    public function registerMetaboxes() {
        // 26.11.2019
        // Removed the registration of the fetch meta box, as it is no longer supported to add single event posts from
        // inside the window of an event post
    }

    /**
     * CHANGELOG
     *
     * Added 04.01.2019
     *
     * Changed 04.01.2019
     * Added an if statement, that first checks if the post type exists already before creating a new one. This will
     * prevent errors, when the overall register is called in multiple plugins.
     */
    public function registerPostType() {
        if (!post_type_exists($this->post_type)) {
            // Creating the arguments array, which specifies the details of what kind of post type it is supposed to be
            $args = array(
                'label'                 => 'Event',
                'description'           => 'Describes an upcoming indico event',
                'public'                => true,
                'publicly_queryable'    => true,
                'show_ui'               => true,
                'menu_position'         => 5,
                'map_meta_cap'          => true,
                'supports'              => array(
                    'title',
                    'editor',
                    'custom-fields'
                ),
                'menu_icon'             => self::ICON,
            );
            register_post_type($this->post_type, $args);
        }

    }

    /**
     * Registers the custom taxonomies for the creator, type and location of the event in wordpress
     *
     * CHANGELOG
     *
     * Added 04.01.2019
     *
     * Changed 04.01.2019
     * Added an if statement, which checks if the taxonomy already exists. Prevents errors, when the overall register is
     * called multiple times.
     */
    public function registerTaxonomies() {
        // The TAXONOMIES array contains the names, which the taxonomies are supposed to have as keys and the values
        // to those keys are the argument arrays for their registration within wordpress. So everything we have to do
        // is iterate this array and call register for each entry.
        foreach (self::TAXONOMIES as $taxonomy_name => $args) {

            if (!taxonomy_exists($taxonomy_name)) {
                register_taxonomy(
                    $taxonomy_name,
                    $this->post_type,
                    $args
                );
            }
        }
    }

    // *************************************
    // THE SAVING PROCESS FOR THIS POST TYPE
    // *************************************

    /**
     * This function gets hooked in, so it is called every time a new post is being saved into the database, but it
     * will only execute the relevant code, if the post is of the type "Event".
     *
     * CHANGELOG
     *
     * Added 04.01.2018
     *
     * @param string $post_id   The wordpress post id of the post that is being saved.
     * @return string
     */
    public function savePost(string $post_id) {
        if (!PostUtil::isSavingPostType('event', $post_id)) {
            return $post_id;
        }

        // This code part gets called when either
        // 1) The post is being saved, triggered by the used pressing the "Save" button within the admin edit panel
        // in this case the info is stored within the $_POST array
        // 2) The post is being saved programmatically by a script calling the "wp_insert_post" function
        return $post_id;
    }

    // *****************************
    // MODIFYING THE ADMIN LIST VIEW
    // *****************************

    /*
     * MODIFYING THE ADMIN COLUMNS
     * What are the admin columns? When being in the admin dashboard and looking at a post type, the first thing that
     * is being displayed is a sort of list view with all the posts. This list view has certain columns, that display
     * certain information about the post. These columns can be modified to display custom data, which better suits the
     * custom post type.
     * This modification is being done by manipulating various filters.
     *
     * The first thing to be done is to register an additional filter to the hook "manage_[posttype]_posts_column"
     * This function gets passed an array of columns to be registered and can be modified with additional ones.
     *
     * Then the hook action hook "manage_[posttype]_posts_custom_column" can be used to echo the content for one
     * specific column.
     *
     * At last, we can implement sorting of the posts by a custom column. For that we first have to implement a filter
     * to the hook "manage_edit-[posttype]-sortable-columns" and add the column keys to that.
     * After that we have to implement custom wordpress query for this column in the "pre_get_posts" hook.
     */

    /**
     * Filter function, which will register the custom columns for the admin list view of the post type.
     *
     * CHANGELOG
     *
     * Added 10.12.2019
     *
     * @param $columns
     * @return array
     */
    public function manageEventColumns($columns) {
        /*
         * $columns is an associative array, which contains a list of all the columns to be registered for the  admin
         * list view of this post type. Simply adding or removing entries from this array should do the trick.
         * The keys of the array are slugs to identify the columns and the values are the headers, which describe the
         * content of the columns within the dashboard
         *
         * The standard array contains the keys "cb", "title", "author", "categories", "tags", "comments", "date"
         */
        $columns = array(
            'cb'            => $columns['cb'],
            'title'         => $columns['title'],
            'indicoID'      => __('Indico ID'),
            'creator'       => __('Creator'),
            'type'          => __('Event Type'),
            'location'      => __('Location'),
            'date'          => $columns['date']
        );

        return $columns;
    }

    /**
     * Callback function for the action hook "manage_event_posts_custom_column". Depending on the passed column key and
     * the post ID, this function will echo the content to be displayed in the corresponding row of the admin list view
     *
     * CHANGELOG
     *
     * Added 10.12.2019
     *
     * @param $column
     * @param $post_id
     */
    public function contentEventColumns($column, $post_id) {
        $event_post = new EventPost($post_id);

        // The indico ID will also be a link, which will redirect to the actual indico entry of that very
        // event.
        if ($column === 'indicoID') {
            $template = '<a href="%s">%s</a>';
            echo sprintf(
                $template,
                $event_post->url,
                $event_post->indico_id
            );
        }

        // The creator will also be a link, which will redirect to a list view, which will only contain
        // event posts of that very creator. The link acts like the application of a filter
        if ($column === 'creator') {
            $terms = wp_get_post_terms($post_id, 'creator');
            if (count($terms) > 0) {
                $template = '<a href="%s?post_type=%s&creator=%s">%s</a>';
                $creator_term = $terms[0];
                echo sprintf(
                    $template,
                    admin_url('edit.php'),
                    $this->post_type,
                    $creator_term->slug,
                    $creator_term->name
                );
            }
        }

        // The type will also be a link, which will redirect to a list view, which will only contain
        // event posts of that very type. The link acts like the application of a filter
        if ($column === 'type') {
            $terms = wp_get_post_terms($post_id, 'type');
            if (count($terms) > 0) {
                $template = '<a href="%s?post_type=%s&type=%s">%s</a>';
                $type_term = $terms[0];
                echo sprintf(
                    $template,
                    admin_url('edit.php'),
                    $this->post_type,
                    $type_term->slug,
                    $type_term->name
                );
            }
        }

        // Since locations usually dont appear twice, it wouldnt make sense to immplement it as a filter link
        if ($column === 'location') {
            echo $event_post->getLocation();
        }
    }

}