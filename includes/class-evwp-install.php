<?php
/**
 * Installation related functions and actions.
 *
 * @author   Jose A. Salim
 * @category Admin
 * @package  EVoucherWP/Classes
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EVoucherWP_Install Class.
 */
class EVoucherWP_Install {

	/** @var array DB updates and callbacks that need to be run per version */
	private static $db_updates = array();

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
		add_filter( 'wpmu_drop_tables', array( __CLASS__, 'wpmu_drop_tables' ) );
	}

	/**
	 * Check EVoucherWP version and run the updater is required.
	 *
	 * This check is done on all requests and runs if he versions do not match.
	 */
	public static function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'evoucherwp_version' ) !== EVoucherWP()->version ) {
			self::install();
			// do_action( 'evoucherwp_updated' );
		}
	}

	/**
	 * Install EVoucherWP.
	 */
	public static function install() {
		global $wpdb;

		if ( ! defined( 'EVWP_INSTALLING' ) ) {
			define( 'EVWP_INSTALLING', true );
		}

		self::create_tables();
		self::create_roles();

		// Register post types
		EVWP_Post_types::register_post_types();

		// Queue upgrades/setup wizard
		$current_evwp_version    = get_option( 'evoucherwp_version', null );
		$current_db_version    = get_option( 'evoucherwp_db_version', null );

		// No versions? This is a new install :)
		if ( is_null( $current_evwp_version ) && is_null( $current_db_version ) ) {
			set_transient( '_evwp_activation_redirect', 1, 30 );
		}
		
		self::update_db_version();
		self::update_evwp_version();

		// Flush rules after install
		flush_rewrite_rules();
	}

	/**
	 * Update EVWP version to current.
	 */
	private static function update_evwp_version() {
		delete_option( 'evoucherwp_version' );
		add_option( 'evoucherwp_version', EVoucherWP()->version );
	}

	/**
	 * Update DB version to current.
	 * @param string $version
	 */
	public static function update_db_version( $version = null ) {
		delete_option( 'evoucherwp_db_version' );
		add_option( 'evoucherwp_db_version', is_null( $version ) ? EVoucherWP()->version : $version );
	}

	/**
	 * Set up the database tables which the plugin needs to function.
	 *
	 * Tables:
	 *		evoucherwp_templates - Table for storing voucher templates - there are user defined.
	 *		evoucher_vouchers - Table for storgin vouchers.
	 *		evoucher_downlaods - Table for storing user and guest downloads.
	 */
	private static function create_tables() {
		global $wpdb;

		$wpdb->hide_errors();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		/**
		 * Before updating with DBDELTA, remove any primary keys which could be
		 * modified due to schema updates.
		 */

		dbDelta( self::get_schema() );
	}

	/**
	 * Get Table schema.
	 * TODO: https://github.com/woothemes/woocommerce/wiki/Database-Description/
	 * @return string
	 */
	private static function get_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}
	

	/* CREATE TABLE {$wpdb->prefix}evoucherwp_vouchers (
	  post_id mediumint(9) NOT NULL,
	  guid VARCHAR(36),
	  security_code VARCHAR(36),
	  PRIMARY KEY  id (id)
	) $collate;

	CREATE TABLE {$wpdb->prefix}evoucherwp_templates (
	  id mediumint(9) NOT NULL,
	  name VARCHAR(255) NOT NULL,
	  fields longtext NOT NULL,
	  PRIMARY KEY  id (id)
	) $collate;*/

		// table to store the vouchers
		$tables = "    
CREATE TABLE {$wpdb->prefix}evoucherwp_downloads (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  voucher_id mediumint(9) NOT NULL,
  time bigint(11) DEFAULT '0' NOT NULL,
  ip VARCHAR(15) NOT NULL,
  name VARCHAR(55) NULL,
  email varchar(255) NULL,
  guid varchar(36) NOT NULL
  PRIMARY KEY  id (id)
) $collate;
	";

		return $tables;
	}

	/**
	 * Create roles and capabilities.
	 */
	public static function create_roles() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		// Voucher manager role
	    add_role( 'evoucherwp_manager', __( 'Vouchers Manager', 'evoucherwp' ), array(
            'level_9'                => true,
            'level_8'                => true,
            'level_7'                => true,
            'level_6'                => true,
            'level_5'                => true,
            'level_4'                => true,
            'level_3'                => true,
            'level_2'                => true,
            'level_1'                => true,
            'level_0'                => true,
            'read'                   => true,
            'read_private_pages'     => true,
            'read_private_posts'     => true,
            'edit_users'             => true,
            'edit_posts'             => true,
            'edit_pages'             => true,
            'edit_published_posts'   => true,
            'edit_published_pages'   => true,
            'edit_private_pages'     => true,
            'edit_private_posts'     => true,
            'edit_others_posts'      => true,
            'edit_others_pages'      => true,
            'publish_posts'          => true,
            'publish_pages'          => true,
            'delete_posts'           => true,
            'delete_pages'           => true,
            'delete_private_pages'   => true,
            'delete_private_posts'   => true,
            'delete_published_pages' => true,
            'delete_published_posts' => true,
            'delete_others_posts'    => true,
            'delete_others_pages'    => true,
            'manage_categories'      => true,
            'manage_links'           => true,
            'moderate_comments'      => true,
            'unfiltered_html'        => true,
            'upload_files'           => true,
            'export'                 => true,
            'import'                 => true,
            'list_users'             => true
	    ) );

		$capabilities = self::get_core_capabilities();

	    foreach ( $capabilities as $cap_group ) {
            foreach ( $cap_group as $cap ) {
                    $wp_roles->add_cap( 'evoucherwp_manager', $cap );
                    $wp_roles->add_cap( 'administrator', $cap );
	        }
	    }
	}

	/**
	 * Get capabilities for EVoucherWP - these are assigned to admin/evoucherwp manager during installation or reset.
	 *
	 * @return array
	 */
	 private static function get_core_capabilities() {
		$capabilities = array();

	    //$capabilities['core'] = array(
	    //        'manage_evoucherwp'
	    //);

	    $capability_types = array( 'evoucher', 'evoucher_template');

	    foreach ( $capability_types as $capability_type ) {

            $capabilities[ $capability_type ] = array(
                    // Post type
                    "edit_{$capability_type}",
                    "read_{$capability_type}",
                    "delete_{$capability_type}",
                    "edit_{$capability_type}s",
                    "edit_others_{$capability_type}s",
                    "publish_{$capability_type}s",
                    "read_private_{$capability_type}s",
                    "delete_{$capability_type}s",
                    "delete_private_{$capability_type}s",
                    "delete_published_{$capability_type}s",
                    "delete_others_{$capability_type}s",
                    "edit_private_{$capability_type}s",
                    "edit_published_{$capability_type}s",

                    // Terms
                    //"manage_{$capability_type}_terms",
                    //"edit_{$capability_type}_terms",
                    //"delete_{$capability_type}_terms",
                    //"assign_{$capability_type}_terms"
            );
	    }

	    return $capabilities;
	}

	/**
	 * evoucherwp_remove_roles function.
	 */
	public static function remove_roles() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		$capabilities = self::get_core_capabilities();

		foreach ( $capabilities as $cap_group ) {
			foreach ( $cap_group as $cap ) {
				$wp_roles->remove_cap( 'evoucherwp_manager', $cap );
				$wp_roles->remove_cap( 'administrator', $cap );
			}
		}
	}

	// /**
	//  * Show plugin changes. Code adapted from W3 Total Cache.
	//  */
	// public static function in_plugin_update_message( $args ) {
	// 	$transient_name = 'wc_upgrade_notice_' . $args['Version'];

	// 	if ( false === ( $upgrade_notice = get_transient( $transient_name ) ) ) {
	// 		$response = wp_safe_remote_get( 'https://plugins.svn.wordpress.org/woocommerce/trunk/readme.txt' );

	// 		if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
	// 			$upgrade_notice = self::parse_update_notice( $response['body'], $args['new_version'] );
	// 			set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
	// 		}
	// 	}

	// 	echo wp_kses_post( $upgrade_notice );
	// }

	// /**
	//  * Parse update notice from readme file.
	//  *
	//  * @param  string $content
	//  * @param  string $new_version
	//  * @return string
	//  */
	// private static function parse_update_notice( $content, $new_version ) {
	// 	// Output Upgrade Notice.
	// 	$matches        = null;
	// 	$regexp         = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( WC_VERSION ) . '\s*=|$)~Uis';
	// 	$upgrade_notice = '';

	// 	if ( preg_match( $regexp, $content, $matches ) ) {
	// 		$version = trim( $matches[1] );
	// 		$notices = (array) preg_split('~[\r\n]+~', trim( $matches[2] ) );

	// 		// Check the latest stable version and ignore trunk.
	// 		if ( $version === $new_version && version_compare( WC_VERSION, $version, '<' ) ) {

	// 			$upgrade_notice .= '<div class="wc_plugin_upgrade_notice">';

	// 			foreach ( $notices as $index => $line ) {
	// 				$upgrade_notice .= wp_kses_post( preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line ) );
	// 			}

	// 			$upgrade_notice .= '</div> ';
	// 		}
	// 	}

	// 	return wp_kses_post( $upgrade_notice );
	// }

	/**
	 * Uninstall tables when MU blog is deleted.
	 * @param  array $tables
	 * @return string[]
	 */
	public static function wpmu_drop_tables( $tables ) {
		global $wpdb;

		//$tables[] = $wpdb->prefix . 'evoucherwp_vouchers';
		//$tables[] = $wpdb->prefix . 'evoucherwp_templates';
		$tables[] = $wpdb->prefix . 'evoucherwp_downloads';

		return $tables;
	}

	/**
	 * Get slug from path
	 * @param  string $key
	 * @return string
	 */
	private static function format_plugin_slug( $key ) {
		$slug = explode( '/', $key );
		$slug = explode( '.', end( $slug ) );
		return $slug[0];
	}
}

EVoucherWP_Install::init();
