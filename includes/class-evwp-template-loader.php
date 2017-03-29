<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Template Loader
 * This file was adapted from https://github.com/woocommerce/woocommerce/blob/master/includes/class-wc-template-loader.php
 *
 * @class 		EVWP_Template_Loader
 * @version		2.2.0
 * @package		EVoucherWP/Classes
 * @category	Class
 * @author 		Jose A. Salim
 */
class EVWP_Template_Loader {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_filter( 'template_include', array( __CLASS__, 'template_loader' ), 10 );
	}

	/**
	 * Load a template.
	 *
	 * Handles template usage so that we can use our own templates instead of the themes.
	 *
	 * Templates are in the 'templates' folder. EvoucherWP looks for theme.
	 * overrides in /theme/evoucherwp/ by default.
	 *
	 * For beginners, it also looks for a evoucherwp.php template first. If the user adds.
	 * this to the theme (containing a evoucerwp() inside) this will be used for all.
	 * evoucherwp templates.
	 *
	 * @param mixed $template
	 * @return string
	 */
	public static function template_loader( $template ) {
		$find = array( 'evoucherwp.php' );
		$file = '';

		if ( is_embed() ) {
			return $template;
		}

		if ( is_single() && get_post_type() == 'evoucher' ) {

			$file 	= 'single-evoucher.php';
			$find[] = $file;
			$find[] = EVoucherWP()->template_path() . $file;
		}

		if ( $file ) {
			$template       = locate_template( array_unique( $find ) );
			if ( ! $template ) {
				$template = apply_filters( 'evoucherwp_template_loader', EVoucherWP()->plugin_path() . '/templates/' . $file, $file );
			}
		}

		return $template;
	}
}

EVWP_Template_Loader::init();
