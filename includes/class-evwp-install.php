<?php
/**
 * Installation related functions and actions.
 * This file was adapted from https://github.com/woocommerce/woocommerce/blob/master/includes/class-wc-install.php
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
 * EVWP_Install Class.
 */
class EVWP_Install {

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
			//TODO: do_action( 'evoucherwp_updated' );
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

		self::create_options();	
		self::create_tables();
		self::create_roles();

		// Register post types
		EVWP_Post_types::register_post_types();

		// Queue upgrades/setup wizard
		$current_evwp_version    = get_option( 'evoucherwp_version', null );
		$current_db_version    = get_option( 'evoucherwp_db_version', null );

		// No versions? This is a new install :)
		if ( is_null( $current_evwp_version ) && is_null( $current_db_version ) ) {
			set_transient( '_evoucherwp_activation_redirect', 1, 30 );
		}
		
		self::update_db_version();
		self::update_evoucherwp_version();

		// Flush rules after install
		flush_rewrite_rules();
	}

	/**
	 * Update EVWP version to current.
	 */
	private static function update_evoucherwp_version() {
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
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 */
	private static function create_options() {
		// Include settings so that we can run through defaults
		include_once( 'admin/class-evwp-admin-settings.php' );

		$settings = EVWP_Admin_Settings::get_settings_pages();

		foreach ( $settings as $section ) {
			if ( ! method_exists( $section, 'get_settings' ) ) {
				continue;
			}
			$subsections = array_unique( array_merge( array( '' ), array_keys( $section->get_sections() ) ) );

			foreach ( $subsections as $subsection ) {
				foreach ( $section->get_settings( $subsection ) as $value ) {
					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}
		}
	}

	/**
	 * Set up the database tables which the plugin needs to function.
	 *
	 * Tables:
	 *		evoucherwp_templates - Table for storing voucher templates - there are user defined.
	 *		evoucher_vouchers - Table for storgin vouchers.
	 *		evoucher_downlaods - Table for storing user and guest downloads.
	 * TODO: remove it
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
	 * TODO: remove it? Is it necessary?
	 * @return string
	 */
	private static function get_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		// table to store the vouchers
		$tables = "
CREATE TABLE {$wpdb->prefix}evoucherwp_code_seq (
	code bigint(20) NOT NULL AUTO_INCREMENT,
	post_id bigint(20) NOT NULL,
	UNIQUE KEY post_id ( post_id ),
	PRIMARY KEY (code)
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

		$capabilities[ 'core' ] = array(
			'manage_evoucherwp'
		);

	    $capability_types = array( 'evoucher' );

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
                    "edit_published_{$capability_type}s"
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

		$tables[] = $wpdb->prefix . 'evoucherwp_code_seq';

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

EVWP_Install::init();
