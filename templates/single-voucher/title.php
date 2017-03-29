<?php
/**
 * Single Voucher title
 *
 * This template can be overridden by copying it to yourtheme/evoucherwp/single-voucher/title.php.
 *
 * @author     Jose A. Salim
 * @package    EVoucherWP/Templates
 * @version    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$title = apply_filters( 'evoucherwp_template_title', the_title( '<h1 itemprop="name" class="voucher-title entry-title">', '</h1>', false ) );
echo $title;
