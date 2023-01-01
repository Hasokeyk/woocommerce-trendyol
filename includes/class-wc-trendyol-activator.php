<?php

/**
 * Fired during plugin activation
 *
 * @link       https://hayatikodla.net/hasan-yuksektepe-kimdir/
 * @since      1.0.0
 *
 * @package    Wc_Trendyol
 * @subpackage Wc_Trendyol/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wc_Trendyol
 * @subpackage Wc_Trendyol/includes
 * @author     Hasan Yüksektepe <hasanhasokeyk@hotmail.com>
 */
class Wc_Trendyol_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate($welcome = false) {
		do_action( 'wc_trendyol_welcome', $welcome );
	}

}
