<?php
/*
Plugin Name: Video interviews
Plugin URI: http://www.tobiaswright.com
Description: Video interview plugin
Version: 1.0
Author: Tobias Wright
Author URI: http://www.tobiaswright.com
License: GPLv2
*/

// Curl helper function
function curl_get($url) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    $return = curl_exec($curl);
    curl_close($curl);
    return $return;
}

// Creates Movie Reviews Custom Post Type
function video_interviews_init() {
    $args = array(
      'label' => 'Video interviews',
        'public' => true,
        'show_ui' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'rewrite' => array('slug' => 'video-interviews'),
        'query_var' => true,
        'menu_icon' => 'dashicons-video-alt',
        'supports' => array(
            'title',
            'editor',
            'excerpt',
            'custom-fields',
            'revisions',
            'thumbnail',
            'author',
            'page-attributes',)
        );
    register_post_type( 'video-interviews', $args );
}

//Sets up post type
add_action( 'init', 'video_interviews_init' );

function video_url_admin() {
    add_meta_box( 'video_url_meta_box',
        'Video URL',
        'display_video_url_meta_box',
        'video-interviews', 'normal', 'high'
    );
}

function display_video_url_meta_box( $video_url ) {
    // Retrieve current name of the Director and Movie Rating based on review ID
    $video_embed = esc_html( get_post_meta( $video_url->ID, 'video_embed', true ) );
    $video_thumb = esc_html( get_post_meta( $video_url->ID, 'video_thumb', true ) );
    ?>
        <textarea style="width:100%;height:100px" rows="5" cols="40" name="video_url_meta_box" ><?php echo $video_embed; ?></textarea>

        <p><strong>Current thumbnail</strong></p>
        <img width='100%' src="<?php echo $video_thumb; ?>" />
    <?php
}

//Sets special custom meta box
add_action( 'admin_init', 'video_url_admin' );

function add_video_url( $video_url_id, $video_url ) {
    // Check post type for movie reviews
    if ( $video_url->post_type == 'video-interviews' ) {
        // Store data in post meta table if present in post data
        if ( isset( $_POST['video_url_meta_box'] ) && $_POST['video_url_meta_box'] != '' ) {
            update_post_meta( $video_url_id, 'video_embed', $_POST['video_url_meta_box'] );

            //save video thumbnail
            $get_info = simplexml_load_string( curl_get('https://vimeo.com/api/oembed.xml?url=' .urlencode($_POST['video_url_meta_box'])));
            $video_tb = (string)$get_info->thumbnail_url;
            update_post_meta( $video_url_id, 'video_thumb', $video_tb);
            $video_id = (string)$get_info->video_id;
            update_post_meta( $video_url_id, 'video_id', $video_id);
        }
    }
}

//Sets up save for special meta box
add_action( 'save_post', 'add_video_url', 10, 2 );
?>