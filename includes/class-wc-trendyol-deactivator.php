<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://hayatikodla.net/hasan-yuksektepe-kimdir/
 * @since      1.0.0
 *
 * @package    Wc_Trendyol
 * @subpackage Wc_Trendyol/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Wc_Trendyol
 * @subpackage Wc_Trendyol/includes
 * @author     Hasan Yüksektepe <hasanhasokeyk@hotmail.com>
 */
class Wc_Trendyol_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		as_unschedule_action('wc_trendyol_trendyol_product_exists_control');
	}

}
