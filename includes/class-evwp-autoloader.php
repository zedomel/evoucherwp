<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EVoucherWP Autoloader.
 *
 * @class 		EVWP_Autoloader
 * @version		1.0.0
 * @package		EVoucherWP/Classes
 * @category	Class
 * @author 		Jose A. Salim
 */
class EVWP_Autoloader {

	/**
	 * Path to the includes directory.
	 *
	 * @var string
	 */
	private $include_path = '';

	/**
	 * The Constructor.
	 */
	public function __construct() {
		if ( function_exists( "__autoload" ) ) {
			spl_autoload_register( "__autoload" );
		}

		spl_autoload_register( array( $this, 'autoload' ) );

		$this->include_path = untrailingslashit( plugin_dir_path( EVWP_PLUGIN_FILE ) ) . '/includes/';
	}

	/**
	 * Take a class name and turn it into a file name.
	 *
	 * @param  string $class
	 * @return string
	 */
	private function get_file_name_from_class( $class ) {
		return 'class-' . str_replace( '_', '-', $class ) . '.php';
	}

	/**
	 * Include a class file.
	 *
	 * @param  string $path
	 * @return bool successful or not
	 */
	private function load_file( $path ) {
		if ( $path && is_readable( $path ) ) {
			include_once( $path );
			return true;
		}
		return false;
	}

	/**
	 * Auto-load WC classes on demand to reduce memory consumption.
	 *
	 * @param string $class
	 */
	public function autoload( $class ) {
		$class = strtolower( $class );
		$file  = $this->get_file_name_from_class( $class );
		$path  = '';

		if ( strpos( $class, 'evwp_meta_box' ) === 0 ) {
			$path = $this->include_path . 'admin/metaboxes/';
		} elseif ( strpos( $class, 'evwp_admin' ) === 0 ) {
			$path = $this->include_path . 'admin/';
		}

		if ( empty( $path ) || ( ! $this->load_file( $path . $file ) && strpos( $class, 'evwp_' ) === 0 ) ) {
			$this->load_file( $this->include_path . $file );
		}
	}
}

new EVWP_Autoloader();
