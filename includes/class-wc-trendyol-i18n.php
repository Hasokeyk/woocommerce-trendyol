<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://hayatikodla.net/hasan-yuksektepe-kimdir/
 * @since      1.0.0
 *
 * @package    Wc_Trendyol
 * @subpackage Wc_Trendyol/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wc_Trendyol
 * @subpackage Wc_Trendyol/includes
 * @author     Hasan Yüksektepe <hasanhasokeyk@hotmail.com>
 */
class Wc_Trendyol_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wc-trendyol',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
