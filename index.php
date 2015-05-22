<?php
/*

  Plugin Name: Hide Featured Image

  Plugin URI: http://shahpranav.com/

  Description: To show/hide featured images on individual posts.

  Version: 1.0

  Author: shahpranaf

  Author URI: http://shahpranav.com/

  License: GPLv2 or later

 */


// Actions and hooks
add_action( 'add_meta_boxes', 'sh_post_types_custom_box' ); // WP 3.0+
add_action( 'admin_init', 'sh_post_types_custom_box', 1 ); // backwards compatible
add_action( 'save_post', 'sh_post_types_save_postdata' ); /* Do something with the data entered */
add_action('wp_head', 'sh_featured_image');

/**
 *  Adds a box to the main column on the Post and Page edit screens
 * 
 * @since Hide Featured Image 1.0
 */
function sh_post_types_custom_box() {
    
    global $sh_post_types;
    $sh_post_types = get_post_types( '', 'names' );
    unset($sh_post_types['page'], $sh_post_types['attachment'], $sh_post_types['revision'], $sh_post_types['nav_menu_item'] );

    foreach ($sh_post_types as $post_type) {
        add_meta_box( 'hide_featured', __( 'Show or Hide Featured Image', 'Hide Image' ), 'sh_featured_box', $post_type, 'side', 'default' );
    }
       
}

/**
 * Add metabox to posts.
 */
function sh_featured_box($post){
    wp_nonce_field( plugin_basename( __FILE__ ), $post->post_type . '_noncename' );

    $hide_featured = get_post_meta( $post->ID, '_hide_featured', true ); ?>
    <input type="checkbox" name="_hide_featured" value='1' <?php checked( 1 == $hide_featured ); ?> /> <?php _e('Hide Featured Image','hide-featured-image');
}

/** 
 * When the post is saved, saves our custom data 
 * 
 * @since Hide Featured Image 1.0
 */
function sh_post_types_save_postdata( $post_id ) {
    
    global $sh_post_types;

    // verify if this is an auto save routine. 
    // If it is our form has not been submitted, so we dont want to do anything
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;

    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times

    if ( !wp_verify_nonce( @$_POST[$_POST['post_type'] . '_noncename'], plugin_basename( __FILE__ ) ) )
      return;

    // OK,nonce has been verified and now we can save the data according the the capabilities of the user
    if( in_array($_POST['post_type'], $sh_post_types) ) {
      if ( !current_user_can( 'edit_page', $post_id ) ) {
          return;
      } else {
    			$hide_featured = ( isset( $_POST['_hide_featured'] ) && $_POST['_hide_featured'] == 1 ) ? '1' : '';
    			update_post_meta( $post_id, '_hide_featured', $hide_featured );			
      }
    }
}

/**
 *  To hide featured image from single post page
 * 
 * @since Hide Featured Image 1.0
 */
function sh_featured_image() {
    
    if( is_single() ){
        $hide_image =  get_post_meta( get_the_ID(), '_hide_featured', true );

        if( isset( $hide_image ) && $hide_image ){ ?>
            <style>
            .has-post-thumbnail img.wp-post-image{ display: none; }
            </style><?php
        }
    }
}
?>