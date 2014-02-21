<?php
/**
 * Plugin Name:     Remove Administrators
 * Description:     Allows admins to hide the admin role from all other roles
 * Version:         1.0.1
 * Author:          Daniel J Griffiths
 * Author URI:      http://section214.com
 * Text Domain:     remove-administrators
 *
 * @package         RemoveAdministrators
 * @author          Daniel J Griffiths <dgriffiths@section214.com
 * @copyright       Copyright (c) 2012-2014, Daniel J Griffiths
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


if( !class_exists( 'Remove_Administrators' ) ) {

    /**
     * Main Remove_Administrators class
     *
     * @since       1.0.1
     */
    class Remove_Administrators {

        /**
         * @var         Remove_Administrators $instance The one true Remove_Administrators
         * @since       1.0.1
         */
        private static $instance;

        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.1
         * @return      object self::$instance The one true Remove_Administrators
         */
        public static function instance() {
            if( !self::$instance ) {
                self::$instance = new Remove_Administrators();
                self::$instance->setup_constants();
                self::$instance->load_textdomain();
                self::$instance->hooks();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.1
         * @return      void
         */
        private function setup_constants() {
            // Plugin path
            define( 'REMOVE_ADMINISTRATORS_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'REMOVE_ADMINISTRATORS_URL', plugin_dir_url( __FILE__ ) );
        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function hooks() {
            // Edit plugin metalinks
            add_filter( 'plugin_row_meta', array( $this, 'plugin_metalinks' ), null, 2 );

            // Enqueue jQuery
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_jquery' ) );

            // Remove admins from editable roles
            add_action( 'editable_roles', array( $this, 'edit_editable_roles' ) );

            // Hide admins from user list
            add_action( 'admin_head', array( $this, 'edit_user_list' ) );
        }


        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.1
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
            $lang_dir = apply_filters( 'Remove_Administrators_lang_dir', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale     = apply_filters( 'plugin_locale', get_locale(), '' );
            $mofile     = sprintf( '%1$s-%2$s.mo', 'remove-administrators', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/remove-administrators/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/remove-administrators/ folder
                load_textdomain( 'remove-administrators', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/remove-administrators/languages/ folder
                load_textdomain( 'remove-administrators', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'remove-administrators', false, $lang_dir );
            }
        }


        /**
         * Modify plugin metalinks
         *
         * @access      public
         * @since       1.1.0
         * @param       array $links The current links array
         * @param       string $file A specific plugin table entry
         * @return      array $links The modified links array
         */
        public function plugin_metalinks( $links, $file ) {
            if( $file == plugin_basename( __FILE__ ) ) {
                $help_link = array(
                    '<a href="http://section214.com/support/forum/remove-administrators/" target="_blank">' . __( 'Support Forum', 'remove-administrators' ) . '</a>'
                );

                $docs_link = array(
                    '<a href="http://section214.com/docs/category/remove-administrators/" target="_blank">' . __( 'Docs', 'remove-administrators' ) . '</a>'
                );

                $links = array_merge( $links, $help_link, $docs_link );
            }

            return $links;
        }


        /**
         * Enqueue jQuery
         *
         * @access      public
         * @since       1.0.1
         * @global      string $pagenow The page we are currently on
         * @return      void
         */
        public function enqueue_jquery() {
            global $pagenow;

        	( 'users.php' == $pagenow ) && wp_enqueue_script( 'jquery' );
        }


        /**
         * Remove admins from editable roles
         *
         * @access      public
         * @since       1.0.1
         * @param       array $roles The current roles list
         * @return      array $roles The filtered roles list
         */
        public function edit_editable_roles( $roles ) {
        	if( isset( $roles['administrator'] ) && !current_user_can( 'update_core' ) ) {
        		unset( $roles['administrator'] );
        	}
        
            return $roles;
        }


        /**
         * Hide admins from the user list
         *
         * @access      public
         * @since       1.0.1
         * @return
         */
        public function edit_user_list() {
        	if( !current_user_can( 'update_core' ) ) { ?>
<script type='text/javascript'>
	jQuery(document).ready(function() {
		var admin_count;
		var total_count;

		jQuery(".subsubsub > li > a:contains(Administrator)").each(function() {
			admin_count = jQuery(this).children('.count').text();
			admin_count = admin_count.substring(1, admin_count.length - 1);
		});
		jQuery(".subsubsub > li > a:contains(Administrator)").parent().remove();
		jQuery(".subsubsub > li > a:contains(All)").each(function() {
			total_count = jQuery(this).children('.count').text();
			total_count = total_count.substring(1, total_count.length - 1) - admin_count;
			jQuery(this).children('.count').text('('+total_count+')');
		});
		jQuery("#the-list > tr > td:contains(Administrator)").parent().remove();
	});
</script>
        	<?php }
        }
    }
}


/**
 * The main function responsible for returning the one true Remove_Administrators
 * instance to functions everywhere
 *
 * @since       1.0.1
 * @return      Remove_Administrators The one true Remove_Administrators
 */
function Remove_Administrators_load() {
    return Remove_Administrators::instance();
}
add_action( 'plugins_loaded', 'Remove_Administrators_load' );
