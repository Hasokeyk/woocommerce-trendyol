<?php

	/**
	 * The public-facing functionality of the plugin.
	 *
	 * @link       https://hayatikodla.net/hasan-yuksektepe-kimdir/
	 * @since      1.0.0
	 *
	 * @package    Wc_Trendyol
	 * @subpackage Wc_Trendyol/public
	 */

	/**
	 * The public-facing functionality of the plugin.
	 *
	 * Defines the plugin name, version, and two examples hooks for how to
	 * enqueue the public-facing stylesheet and JavaScript.
	 *
	 * @package    Wc_Trendyol
	 * @subpackage Wc_Trendyol/public
	 * @author     Hasan Yüksektepe <hasanhasokeyk@hotmail.com>
	 */
	class Wc_Trendyol_Public{

		/**
		 * The ID of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string $plugin_name The ID of this plugin.
		 */
		private $plugin_name;

		/**
		 * The version of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string $version The current version of this plugin.
		 */
		private $version;

		/**
		 * Initialize the class and set its properties.
		 *
		 * @param string $plugin_name The name of the plugin.
		 * @param string $version The version of this plugin.
		 *
		 * @since    1.0.0
		 */
		public function __construct($plugin_name, $version){

			$this->plugin_name = $plugin_name;
			$this->version     = $version;

			//PRODUCT DETAIL NEW TAB
			add_filter('woocommerce_product_tabs', [$this, 'wc_trendyol_product_detail_tab']);
			//PRODUCT DETAIL NEW TAB

		}

		//PRODUCT DETAIL NEW TAB
		function wc_trendyol_product_detail_tab($tabs){
            global $post;

            $get_wc_trendyol_customer_question = get_post_meta($post->ID,'wc_trendyol_show_customer_questions',true);
            if($get_wc_trendyol_customer_question == 1){
	            $tabs['test_tab'] = [
		            'title'    => __('Trendyol Soru & Cevap', 'wc-trendyol'),
		            'priority' => 50,
		            'callback' => [$this, 'wc_trendyol_product_detail_tab_content'],
	            ];
            }

			return $tabs;
		}

		function wc_trendyol_product_detail_tab_content(){
			global $trendyol_adapter, $post;

			$sku                = get_post_meta($post->ID, 'wc_trendyol_trendyol_product_code', true);
			$trendyol_questions = $trendyol_adapter->get_product_questions($sku);

			echo '<div class="wc_trendyol_question_content">';
			if(isset($trendyol_questions->result->content[0])){

				foreach($trendyol_questions->result->content as $question){
					?>
                    <div class="wc_trendyol_question">
                        <div class="wc_trendyol_customer_question">
                            <div class="title"><?php _e('Müşteri Sorusu', 'wc-trendyol'); ?></div>
							<?=mb_convert_case($question->text, MB_CASE_TITLE_SIMPLE, 'utf8');?>
                        </div>
                        <div class="wc_trendyol_store_answer">
                            <div class="title"><?php _e('Mağaza Cevabı', 'wc-trendyol'); ?></div>
							<?=$question->answer->text?>
                        </div>
                    </div>
					<?php
				}

			}else{
                _e('Bu ürün için soru bulunamadı','wc-trendyol');
			}
			echo '</div>';
		}
		//PRODUCT DETAIL NEW TAB

		/**
		 * Register the stylesheets for the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_styles(){

			/**
			 * This function is provided for demonstration purposes only.
			 *
			 * An instance of this class should be passed to the run() function
			 * defined in Wc_Trendyol_Loader as all of the hooks are defined
			 * in that particular class.
			 *
			 * The Wc_Trendyol_Loader will then create the relationship
			 * between the defined hooks and the functions defined in this
			 * class.
			 */

			wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__).'css/wc-trendyol-public.css', [], $this->version, 'all');

		}

		/**
		 * Register the JavaScript for the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_scripts(){

			/**
			 * This function is provided for demonstration purposes only.
			 *
			 * An instance of this class should be passed to the run() function
			 * defined in Wc_Trendyol_Loader as all of the hooks are defined
			 * in that particular class.
			 *
			 * The Wc_Trendyol_Loader will then create the relationship
			 * between the defined hooks and the functions defined in this
			 * class.
			 */

			wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__).'js/wc-trendyol-public.js', ['jquery'], $this->version, false);

		}

	}
