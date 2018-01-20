<?php
/**
 * Created by PhpStorm.
 * User: solomashenko
 * Date: 12.03.17
 * Time: 15:22
 */
//error_log("Load child theme-functions");

add_filter('wp_title', 'filterTitle');
function filterTitle($title) {
    return $title.' - Twenty Seventeen Child';
}

/*add_filter('the_content', 'filterTheContent');
function filterTheContent($content) {
    return $content.' - Twenty Seventeen Child';
}*/

add_action('wp_enqueue_scripts', 'my_theme_styles' );
function my_theme_styles() {
    wp_enqueue_style('parent-theme-css', get_template_directory_uri() .'/style.css' );
    wp_enqueue_style('child-theme-css', get_stylesheet_directory_uri() .'/style.css', array('parent-theme-css') );
}

define("MY_THEME_TEXTDOMAIN", 'twentyseventeen-child');

/**
 * Загрузка Text Domain
 */
function childThemeLocalization(){
    load_child_theme_textdomain(MY_THEME_TEXTDOMAIN, get_stylesheet_directory() . '/languages/');
}
add_action('after_setup_theme', 'childThemeLocalization');


add_action('admin_menu', 'addAdminMenu');
function addAdminMenu(){

    $themeMenuPage = add_theme_page(
        __('Sub theme Step By Step', MY_THEME_TEXTDOMAIN),
        __('Sub theme Step By Step', MY_THEME_TEXTDOMAIN),
        'read',
        'twentyseventeen_child_control_sub_theme_menu',
        'renderThemeMenu'
    );
}
function renderThemeMenu(){
    _e('Sub theme Step By Step', MY_THEME_TEXTDOMAIN);
}


add_shortcode( 'twentyseventeen_child_guest_book', 'guestBookShortcode');
function guestBookShortcode(){
    $output = '';
    $output .= '<form  method="post">
                    <label>'.__('User name', MY_THEME_TEXTDOMAIN ).'</label>
                    <input type="text" name="twentyseventeen_child_" class="twentyseventeen-child-name">
                    <label>'.__('Message', MY_THEME_TEXTDOMAIN ).'</label>
                    <textarea name="twentyseventeen_child_message" class="twentyseventeen-child-message"></textarea>
                    <button class="twentyseventeen-child-btn-add" >'.__('Add', MY_THEME_TEXTDOMAIN ).'</button>                   
                </form>';
    return $output;
}

add_action('media_buttons','addMediaButtons');
function addMediaButtons(){
    $button = '<a href="#" id="guestBookShortcodeButton" class="su-generator-button button">'
        .__('Insert shortcode', MY_THEME_TEXTDOMAIN).'</a>';
    echo $button;

}

add_action('admin_enqueue_scripts', 'loadScriptAdmin');
function loadScriptAdmin($hook){
    wp_enqueue_script(
        'twentyseventeen_child_admin_main', //$handle
        get_stylesheet_directory_uri() .'/assets/js/twentyseventeen-child-admin-main.js', //$src
        array(
            'jquery',
        )
    );

}

add_action( 'init', 'setupTinyMCE' );
function setupTinyMCE(){
    add_filter( 'mce_external_plugins', 'addTinyMCE' );
    add_filter( 'mce_buttons', 'addTinyMCEToolbar' );
}

function addTinyMCE( $plugin_array ) {

    $plugin_array['twentyseventeen_child_custom_class'] = get_stylesheet_directory_uri()
        . '/assets/js/MYTinyMCE.js';
    return $plugin_array;

}

function addTinyMCEToolbar( $buttons ) {

    array_push( $buttons, 'guest_book_shortcode_button' );
    return $buttons;

}
function twentyseventeen_custom_header_setup_child() {

    /**
     * Filter Twenty Seventeen custom-header support arguments.
     *
     * @since Twenty Seventeen 1.0
     *
     * @param array $args {
     *     An array of custom-header support arguments.
     *
     *     @type string $default-image     		Default image of the header.
     *     @type string $default_text_color     Default color of the header text.
     *     @type int    $width                  Width in pixels of the custom header image. Default 954.
     *     @type int    $height                 Height in pixels of the custom header image. Default 1300.
     *     @type string $wp-head-callback       Callback function used to styles the header image and text
     *                                          displayed on the blog.
     *     @type string $flex-height     		Flex support for height of header.
     * }
     */
    add_theme_support( 'custom-header', apply_filters( 'twentyseventeen_custom_header_args', array(
        'default-image'      => get_stylesheet_directory_uri(). '/assets/images/header.jpg' ,
        'width'              => 2000,
        'height'             => 1200,
		'flex-height'        => true,
		'video'              => true,
		'wp-head-callback'   => 'twentyseventeen_header_style',
	) ) );

	register_default_headers( array(
		'default-image' => array(
			'url'           => '%s/assets/images/header.jpg',
			'thumbnail_url' => '%s/assets/images/header.jpg',
			'description'   => __( 'Default Header Image', 'twentyseventeen-child' ),
		),
	) );
}
add_action( 'after_setup_theme', 'twentyseventeen_custom_header_setup_child' );

function get_latest_post ( $params ){
    $array = array();
    $post = get_posts( array(
        //'category'      => $params,
        'posts_per_page'  => 100,

    ) );


    if( empty( $post ) ){
        return null;
    }
    else {
        foreach( $post as $myposts ){

            $array[] = array('ID' => $myposts->ID, 'http' => get_post_meta($myposts->ID, "_Photo Source", true));
        }
    }

    return   $array;
}

// Register the rest route here.

add_action( 'rest_api_init', function () {

    register_rest_route( 'wp/v2', 'latest-post',array(

        'methods'  => 'GET',
        'callback' => 'get_latest_post'

    ) );

} );