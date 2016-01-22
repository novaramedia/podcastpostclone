<?php
/**
 * Plugin Name: Novara Podcast Post Clone
 * Plugin URI:
 * Description: Clone post values from latest FM post
 * Version: 1.0.0
 * Author: Interglobal Vision
 * Author URI: http://interglobal.vision
 * License: GPL2
*/

class Novara_Podcast_Post_Clone {
  public function __construct() {
    register_activation_hook( __FILE__, array( $this, 'after_activation' ) );
    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    add_action( 'add_meta_boxes', array( $this, 'add_vimeo_field' ) );
    add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
    add_action( 'admin_init', array( $this, 'settings_init' ) );
  }

  public function after_activation() {

    // Check if previous settins aren't stored
    if( !get_option( 'nmpodclone_settings_post_types' ) ) {
      // Enable Vimeo field on "posts" post type
      update_option( 'nmpodclone_settings_post_types', array(
        0 => 'post'
      ) );
    }
    //delete_option( 'nmpodclone_settings_post_types' );
    //delete_option( 'nmpodclone_settings_whitelist' );
  }

  /**
   * Load JS scripts
   * Only on post.php and post-new.php
   */
  public function enqueue_scripts( $hook ){
    if( 'post.php' != $hook && 'post-new.php' != $hook ) {
      return;
    }

    global $post_type;
    $saved_post_types = get_option( 'nmpodclone_settings_post_types' );

    if( in_array( $post_type, $saved_post_types ) ) {
      wp_register_script( 'novara-podcast-post-clone-script', plugins_url( '/novara-podcast-post-clone.js', __FILE__ ), array( 'jquery' ) );
    }

    // Get plugin options
    $whitelist = get_option( 'nmpodclone_settings_whitelist' );

    // Pass options to js script
/*
    wp_localize_script( 'novara-podcast-post-clone-script', 'nmpodclone', array(
      "whitelist" => $whitelist
    ) );
*/

    // Enqueue script
    wp_enqueue_script( 'novara-podcast-post-clone-script' );
  }

  public function add_vimeo_field() {
    $saved_post_types = get_option( 'nmpodclone_settings_post_types' );

    foreach( $saved_post_types as $post_type ) {
      add_meta_box(
        'nmpodclone-vimeo-id-meta-box',
        'CLONERRR',
        array( $this, 'vimeo_id_meta_box_callback' ),
        $post_type
      );
    }
  }

  /**
   * Prints the Vimeo ID box.
   *
   * @patam WP_Post $post The object for the current post.
   */
  public function vimeo_id_meta_box_callback( $post ) {

    // Add an nonce field so we can check for it later.
    wp_nonce_field( 'globie_vimeo_sucker', 'nmpodclone_nonce' );

    echo ' <input type="submit" id="suck-vimeo-data" value="Clone it!" class="button">';
    echo ' <div id="globie-spinner" style="background: url(\'/wp-admin/images/wpspin_light.gif\') no-repeat; background-size: 16px 16px; display: none; opacity: .7; filter: alpha(opacity=70); width: 16px; height: 16px; margin: 0 10px;"></div>';
  }

  public function add_admin_menu() {
    add_options_page(
      'Novara Podcast Post Clone Options',
      'Podcast Post Clone',
      'manage_options',
      'novara-podcast-post-clone',
      array( $this, 'options_page' )
    );
  }

  // Register settings, sections and fields
  public function settings_init() {
    // Register option: post types
    register_setting( 'nmpodclone_options_page', 'nmpodclone_settings_post_types' );

    // Add post type section
    add_settings_section(
      'nmpodclone_post_types_section',
      __( 'Enable/Disable on post types', 'wordpress' ),
      array( $this, 'settings_section_callback' ),
      'nmpodclone_options_page'
    );

    // Post Types fields
    add_settings_field(
      'nmpodclone_post_types_fields',
      __( 'Post types', 'wordpress' ),
      array( $this, 'settings_post_types_fields_render' ),
      'nmpodclone_options_page',
      'nmpodclone_post_types_section'
    );

    // Register option: whitelist
    register_setting( 'nmpodclone_options_page', 'nmpodclone_settings_whitelist' );

    // Add whitelist section
    add_settings_section(
      'nmpodclone_whitelist_section',
      __( 'Tags whitelist', 'wordpress' ),
      array( $this, 'settings_whitelist_section_callback' ),
      'nmpodclone_options_page'
    );

    // Whitelist field
    add_settings_field(
      'nmpodclone_whitelist_fields',
      __( 'Tags', 'wordpress' ),
      array( $this, 'settings_whitelist_field_render' ),
      'nmpodclone_options_page',
      'nmpodclone_whitelist_section'
    );
  }

  public function settings_post_types_fields_render() {
    // Get options saved
    $saved_post_types = get_option( 'nmpodclone_settings_post_types' );

    // Get post types
    $post_types= get_post_types(
      array(
        'public' => true
      )
    );

    // Render fields
    echo "<fieldset>";
    foreach( $post_types as $post_type ) {
      $checked = '';

      // Check if field is checked
      if( !empty( $saved_post_types ) && in_array($post_type, $saved_post_types) )
        $checked = 'checked';

      echo '<label for="nmpodclone_settings_post_types[' . $post_type . ']"><input type="checkbox" name="nmpodclone_settings_post_types[]" id="nmpodclone_settings_post_types[' . $post_type . ']" value="' . $post_type . '" ' . $checked . '> ' .  ucfirst($post_type) . '</label><br />';
    }
    echo "</fieldset>";
  }

  public function settings_section_callback() {
    echo __( 'Select the post types where you want to enable the Vimeo ID field', 'wordpress' );
  }

  public function settings_whitelist_field_render() {

    // Get options saved
    $whitelist = get_option( 'nmpodclone_settings_whitelist' );

    // Render fields
    echo "<fieldset>";
    echo '<label for="nmpodclone_input_whitelist" style="width: 100%;"><input type="text" style="width: 100%;" name="nmpodclone_settings_whitelist" id="nmpodclone_input_whitelist" value="' . $whitelist  . '"></label><br />';
    echo "</fieldset>";
  }

  public function settings_whitelist_section_callback() {
    echo __( 'Comma separated list of whitelisted tags', 'wordpress' );
  }

  public function options_page() {
    echo '<form action="options.php" method="post">';
    echo '<h2>Novara Podcast Post Clone Options</h2>';

    settings_fields( 'nmpodclone_options_page' );
    do_settings_sections( 'nmpodclone_options_page' );
    submit_button();

    echo '</form>';

  }
}
$nmpodclone = new Novara_Podcast_Post_Clone();

function pr( $var ) {
  echo '<pre>';
  print_r( $var );
  echo '</pre>';
}
