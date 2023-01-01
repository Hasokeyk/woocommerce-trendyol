<?php

	/**
	 * The admin-specific functionality of the plugin.
	 *
	 * @link       https://hayatikodla.net/hasan-yuksektepe-kimdir/
	 * @since      1.0.0
	 *
	 * @package    Wc_Trendyol
	 * @subpackage Wc_Trendyol/admin
	 */

	/**
	 * The admin-specific functionality of the plugin.
	 *
	 * Defines the plugin name, version, and two examples hooks for how to
	 * enqueue the admin-specific stylesheet and JavaScript.
	 *
	 * @package    Wc_Trendyol
	 * @subpackage Wc_Trendyol/admin
	 * @author     Hasan Yüksektepe <hasanhasokeyk@hotmail.com>
	 */
	class Wc_Trendyol_Admin{

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
		 * @param string $plugin_name The name of this plugin.
		 * @param string $version The version of this plugin.
		 *
		 * @since    1.0.0
		 */
		public function __construct( $plugin_name, $version ) {

			$this->plugin_name = $plugin_name;
			$this->version     = $version;

			//ADD MENU
			add_action( 'admin_menu', [
				$this,
				'trendyol_add_menu',
			] );
			//ADD MENU

			//ADD WOOCOMMERCE PRODUCT CATEGORY FIELDS
			add_action( 'product_cat_add_form_fields', [ $this, 'wc_trendyol_add_wc_category_field' ], 10, 2 );
			add_action( 'product_cat_edit_form_fields', [ $this, 'wc_trendyol_add_wc_category_field' ], 10, 2 );
			add_action( "created_product_cat", [ $this, "wc_trendyol_add_wc_category_field_save" ] );
			add_action( "edited_product_cat", [ $this, "wc_trendyol_add_wc_category_field_save" ] );
			//ADD WOOCOMMERCE PRODUCT CATEGORY FIELDS

			//ADD WOOCOMMERCE PRODUCT CATEGORY COLUMN
			add_action( 'manage_edit-product_cat_columns', [ $this, 'trendyol_add_wc_category_column' ], 10, 2 );
			add_action( 'manage_product_cat_custom_column', [ $this, 'trendyol_add_wc_category_column_data' ], 10, 3 );
			//ADD WOOCOMMERCE PRODUCT CATEGORY COLUMN

			//ADD TRENDYOL PRICE FIELD
			//add_action('woocommerce_product_options_pricing', [$this, 'wc_trendyol_add_basic_product_price_field']);
			//add_action('woocommerce_variation_options_pricing', [$this, 'wc_trendyol_add_variation_product_price_field'], 10, 3);
			//ADD TRENDYOL PRICE FIELD

			//SAVE TRENDYOL PRICE
			add_action( 'woocommerce_process_product_meta', [ $this, 'wc_trendyol_save_basic_production_price_field' ] );
			//SAVE TRENDYOL PRICE

			//ADD WC PRODUCT LIST CUSTOM COL
			add_filter( 'manage_edit-product_columns', [ $this, 'woocommerce_trendyol_add_product_table_column' ], 20 );
			add_action( 'manage_product_posts_custom_column', [ $this, 'woocommerce_trendyol_add_product_table_column_data' ], 2 );
			//ADD WC PRODUCT LIST CUSTOM COL

			//ADD AJAX
			add_action( 'wp_ajax_wc_trendyol_search_brand', [ $this, 'wc_trendyol_search_brand' ] );
			add_action( 'wp_ajax_wc_trendyol_add_product', [ $this, 'wc_trendyol_add_product' ] );
			//ADD AJAX

			//ADD WC TAB
			add_filter( 'woocommerce_product_data_tabs', [ $this, 'wc_trendyol_add_woocommerce_product_tab' ], 10, 1 );
			add_action( 'woocommerce_product_data_panels', [ $this, 'wc_trendyol_add_woocommerce_product_tab_content' ] );
			//ADD WC TAB

			//WOOCOMMERCE BACKGROUND PROCESING
			add_action( 'init', function() {
				add_action( 'wc_trendyol_trendyol_product_exists_control', [ $this, 'wc_trendyol_trendyol_product_exists_control' ], 10, 3 );
				if ( false === as_has_scheduled_action( 'wc_trendyol_trendyol_product_exists_control' ) ) {
					as_schedule_recurring_action( strtotime( 'midnight tonight' ), ( 60 * 10 ), 'wc_trendyol_trendyol_product_exists_control' );
				}
			} );
			//WOOCOMMERCE BACKGROUND PROCESING

			//REDIRECT WELCOME PAGE
			add_action( 'wc_trendyol_welcome', [ $this, 'wc_trendyol_welcome_set_redirect' ] );
			add_action( 'admin_init', [ $this, 'wc_trendyol_welcome_redirect' ] );
			//REDIRECT WELCOME PAGE
		}

		//REDIRECT WELCOME PAGE
		function wc_trendyol_welcome_set_redirect() {
			if ( ! is_network_admin() ) {
				set_transient( 'wc_trendyol_page_welcome_redirect', 1, 30 );
			}
		}

		function wc_trendyol_welcome_redirect() {
			$redirect = get_transient( 'wc_trendyol_page_welcome_redirect' );
			delete_transient( 'wc_trendyol_page_welcome_redirect' );
			if ( $redirect ) {
				wp_safe_redirect( admin_url( 'admin.php?page=' . rawurlencode( 'trendyol_settings' ) ) );
			}
		}
		//REDIRECT WELCOME PAGE

		//WOOCOMMERCE BACKGROUND PROCESING
		public function wc_trendyol_trendyol_product_exists_control() {
			global $trendyol_adapter;

			$get_wc_products = wc_get_products( [
				'limit'  => - 1,
				'status' => 'publish',
			] );

			if ( $get_wc_products != null ) {
				foreach ( $get_wc_products as $product ) {

					$post_id = $product->get_id();
					$sku     = $product->get_sku();
					if ( ! empty( $sku ) ) {

						$trd_product = $trendyol_adapter->get_my_product( $sku );

						if ( $trd_product->totalElements != null ) {
							update_post_meta( $post_id, 'wc_trendyol_is_trendyol_exists', 1 );
							update_post_meta( $post_id, 'wc_trendyol_is_trendyol_sales_price', $trd_product->content[0]->salePrice );
						} else {
							update_post_meta( $post_id, 'wc_trendyol_is_trendyol_exists', 0 );
							update_post_meta( $post_id, 'wc_trendyol_is_trendyol_sales_price', 0 );
						}

					}

				}
			}

		}
		//WOOCOMMERCE BACKGROUND PROCESING

		//ADD WC TAB
		function wc_trendyol_add_woocommerce_product_tab_content() {
			global $woocommerce, $post, $trendyol_adapter;

			$wc_product                       = wc_get_product( $post->ID );
			$sku                              = $wc_product->get_sku();
			$cat                              = $wc_product->get_category_ids();
			$wc_trendyolduct_trendyol_barcode = get_post_meta( $post->ID, 'wc_trendyolduct_trendyol_barcode', true );

			if ( ! empty( $sku ) ) {
				$wc_trendyolduct = $trendyol_adapter->get_my_product( $sku );
				$wc_trendyolduct = $wc_trendyolduct->content[0] ?? null;
				if ( $wc_trendyolduct != null ) {
					update_post_meta( $post->ID, 'wc_trendyol_trendyol_product_code', $sku );
				}
			} else {
				$wc_trendyolduct = null;
			}

			if ( ! empty( $wc_trendyolduct_trendyol_barcode ) and $wc_trendyolduct == null ) {
				$wc_trendyolduct = $trendyol_adapter->get_my_product( $wc_trendyolduct_trendyol_barcode );
				$wc_trendyolduct = $wc_trendyolduct->content[0] ?? null;
				if ( $wc_trendyolduct != null ) {
					update_post_meta( $post->ID, 'wc_trendyol_trendyol_product_code', $wc_trendyolduct_trendyol_barcode );
				}
			}

			$wc_trendyolduct_sale_price = $wc_trendyolduct->salePrice ?? 0;
			$wc_trendyolduct_list_price = $wc_trendyolduct->listPrice ?? 0;

			//TRENDYOL COMMISSION
			$product_cat_id              = current( $cat );
			$get_trendyol_wc_category_id = get_term_meta( $product_cat_id, 'trendyol_wc_category_id', true );
			if ( ! empty( $get_trendyol_wc_category_id ) ) {
				$get_trendyol_cat_commission = $trendyol_adapter->get_category_info( $get_trendyol_wc_category_id );
			}
			//TRENDYOL COMMISSION

			//PRODUCT BRAND
			$main_brand            = get_option( 'wc_trendyol_main_brand', null ) ?? '';
			$wc_trendyolduct_brand = get_post_meta( $post->ID, 'wc_trendyolduct_brand', true );
			if ( ! empty( $wc_trendyolduct_brand ) ) {
				$brand_explode = explode( ':', $wc_trendyolduct_brand );
			} else if ( ! empty( $main_brand ) ) {
				$brand_explode = explode( ':', $main_brand );
			}
			//PRODUCT BRAND

			//PRODUCT SHOW CUSTOMER QUESTIONS
			$wc_trendyol_show_customer_questions = get_post_meta( $post->ID, 'wc_trendyol_show_customer_questions', true );
			//PRODUCT SHOW CUSTOMER QUESTIONS

			//PRODUCT LOCKED CONTROL
			$product_locked         = $wc_trendyolduct->locked ?? null;
			$product_locked_message = $wc_trendyolduct->lockReason ?? null;
			//PRODUCT LOCKED CONTROL

			?>
            <div id="wc_trendyol_settings_tab" class="wc_trendyol_settings_tab panel woocommerce_options_panel">

				<?php
					if ( $product_locked == 1 ) {
						?>
                        <div class="wc_trendyol_alert"><?=__( 'Ürününüz kitlenmiş sebebi', 'wc-trendyol' )?> :
                            <strong><?=$product_locked_message?></strong>
                        </div>
						<?php
					}
				?>

                <h1 class="wc_trendyol_title">
                    <img class="wc_trendyol_title_img" src="<?php echo plugin_dir_url( __DIR__ ) . '/admin/img/trendyol-logo.jpg'; ?>">
					<?php _e( 'Trendyol Ayarları', 'wc-trendyol' ); ?>
                </h1>

                <div class="wc_trendyol_settings wc_trendyol_col_2">
                    <label><?php _e( 'Stok Kodu', 'wc-trendyol' ); ?></label>
                    <input type="text" value="<?=( ! empty( $sku ) ? $sku : 'SKU YOK' )?>" disabled>
                </div>

                <div class="wc_trendyol_settings wc_trendyol_col_2">
                    <label><?php _e( 'Trendyol Barkodu', 'wc-trendyol' ); ?></label>
                    <input type="text" name="wc_trendyolduct_trendyol_barcode" class="wc_trendyolduct_trendyol_barcode" value="<?php echo( ! empty( $wc_trendyolduct_trendyol_barcode ) ? $wc_trendyolduct_trendyol_barcode : '' ); ?>">
                </div>

				<?php
					if ( $wc_trendyolduct != null ) {
						?>
                        <div class="wc_trendyol_settings wc_trendyol_col_4">
                            <label><?php _e( 'Trendyol Liste Fiyatı', 'wc-trendyol' ); ?> (₺)</label>
                            <input type="text" name="wc_trendyolduct_list_price_input" class="wc_trendyolduct_list_price_input" value="<?php echo $wc_trendyolduct_list_price; ?>">
                        </div>
                        <div class="wc_trendyol_settings wc_trendyol_col_4">
                            <label><?php _e( 'Trendyol Satış Fiyatı', 'wc-trendyol' ); ?> (₺)</label>
                            <input type="text" name="wc_trendyolduct_sales_price_input" class="wc_trendyolduct_sales_price_input" value="<?php echo $wc_trendyolduct_sale_price; ?>">
                        </div>
                        <div class="wc_trendyol_settings wc_trendyol_col_4">
                            <label><?php _e( 'Trendyol Kategori Komisyonu', 'wc-trendyol' ); ?> (₺)</label>
                            <input type="text" name="wc_trendyol_cat_commission_input" class="wc_trendyol_cat_commission_input" data-trendyol_cat_commission="<?php echo $get_trendyol_cat_commission->commission ?? 0 ?>" placeholder="<?php echo $get_trendyol_cat_commission->commission ?? __( 'Kategori Eşleşmemiş' ) ?>" disabled>
                        </div>
                        <div class="wc_trendyol_settings wc_trendyol_col_4">
                            <label><?php _e( 'Trendyol Markası', 'wc-trendyol' ); ?></label>
                            <select name="wc_trendyolduct_brand" class="trendyol_select_2_search">
								<?php
									if ( isset( $brand_explode ) ) {
										?>
                                        <option value="<?=$brand_explode[0]?>:<?=$brand_explode[1]?>" selected><?=$brand_explode[1]?></option>
										<?php
									} else {
										?>
                                        <option value="">Marka Ara</option>
										<?php
									}
								?>

                            </select>
                        </div>
                        <div class="wc_trendyol_settings wc_trendyol_col_12">
                            <label><?php _e( 'Ürün Detayda Müşteri Sorularını Göster', 'wc-trendyol' ); ?></label>
                            <input type="checkbox" name="wc_trendyol_show_customer_questions" class="wc_trendyol_show_customer_questions" <?php echo( ( $wc_trendyol_show_customer_questions == 1 ) ? 'checked' : '' ) ?>>
                        </div>
						<?php
					} else {
						?>
                        <div class="wc_trendyol_alert"><?php _e( 'SKU veya Barkod bilgileri Trendyol ile eşleşmediği için fiyat bölümünü göremiyorsunuz. Lütfen SKU veya Barkodu Trendyoldaki ürün ile aynı olacak şekilde ayarlayınız.', 'wc-trendyol' ); ?></div>
						<?php
					}
				?>

            </div>
			<?php
		}

		public function wc_trendyol_add_woocommerce_product_tab( $default_tabs ) {
			$default_tabs['wc_trendyol_settings_tab'] = [
				'label'    => __( 'Trendyol Ayarları', 'wc-trendyol' ),
				'target'   => 'wc_trendyol_settings_tab',
				'priority' => 60,
				'class'    => [ 'show_if_simple', 'show_if_variable' ],
			];

			return $default_tabs;
		}
		//ADD WC TAB

		//ADD AJAX
		public function wc_trendyol_search_brand() {
			global $trendyol_adapter;
			$query = esc_attr( $_POST['q'] );

			$trendyol_brands = $trendyol_adapter->search_brand( $query );

			foreach ( $trendyol_brands as $brand ) {
				$title    = ( mb_strlen( $brand->name ) > 50 ) ? mb_substr( $brand->name, 0, 49 ) . '...' : $brand->name;
				$return[] = [ $brand->id . ':' . $title, $title . ' (' . $brand->id . ')' ];
			}

			echo json_encode( $return );
			wp_die();
		}

		public function wc_trendyol_add_product() {
			global $trendyol_adapter;
			$query = esc_attr( $_POST['q'] );

			$trendyol_brands = $trendyol_adapter->search_brand( $query );

			foreach ( $trendyol_brands as $brand ) {
				$title    = ( mb_strlen( $brand->name ) > 50 ) ? mb_substr( $brand->name, 0, 49 ) . '...' : $brand->name;
				$return[] = [ $brand->id . ':' . $title, $title . ' (' . $brand->id . ')' ];
			}

			echo json_encode( $return );
			wp_die();
		}
		//ADD AJAX

		//ADD WC PRODUCT LIST CUSTOM COL
		public function woocommerce_trendyol_add_product_table_column( $columns ) {
			$offset          = 5;
			$trendyol_exists = array_slice( $columns, 0, $offset, true ) + [ 'trendyol_exists' => esc_html__( 'Trendyolda var mı?', 'woocommerce-trendyol' ) ] + array_slice( $columns, $offset, null, true );

			$offset         = 7;
			$trendyol_price = array_slice( $trendyol_exists, 0, $offset, true ) + [ 'trendyol_price' => esc_html__( 'Trendyol Satış Fiyatı', 'woocommerce-trendyol' ) ] + array_slice( $trendyol_exists, $offset, null, true );

			return $trendyol_price;
		}

		function woocommerce_trendyol_add_product_table_column_data( $column ) {
			global $post, $trendyol_adapter;

			$wc_product = wc_get_product( $post->ID );
			$sku        = $wc_product->get_sku();
			if ( ! empty( $sku ) ) {

				$product_trendyol_exists = get_post_meta( $post->ID, 'wc_trendyol_is_trendyol_exists', true );
				$product_trendyol_price  = get_post_meta( $post->ID, 'wc_trendyol_is_trendyol_sales_price', true );

				if ( $column == 'trendyol_exists' ) {

					$plugin_dir = plugin_dir_url( __DIR__ );

					if ( $product_trendyol_exists != 0 ) {
						echo( '<img src="' . $plugin_dir . '/admin/img/yes.png">' );
					} else {
						echo( '<img src="' . $plugin_dir . '/admin/img/no.png">' );
					}
				} else if ( $column == 'trendyol_price' ) {

					if ( $product_trendyol_exists != 0 ) {
						echo wc_price( $product_trendyol_price );
					} else {
						echo '-';
					}
				}

			} else {

				if ( $column == 'trendyol_exists' ) {
					echo 'Ürün Eşleştirilmemiş';
				} else if ( $column == 'trendyol_price' ) {
					echo 0;
				}

			}
		}
		//ADD WC PRODUCT LIST CUSTOM COL

		//ADD TRENDYOL PRICE FIELD
		public function wc_trendyol_add_basic_product_price_field( $tabs ) {
			global $woocommerce, $post, $trendyol_adapter;

			$wc_product = wc_get_product( $post->ID );
			$sku        = $wc_product->get_sku();
			$cat        = $wc_product->get_category_ids();

			if ( ! empty( $sku ) ) {

				$wc_trendyolduct = $trendyol_adapter->get_my_product( $sku );
				if ( isset( $wc_trendyolduct->totalElements ) and $wc_trendyolduct->totalElements != 0 ) {

					//PRODUCT PRICE
					$wc_trendyolduct_price = get_post_meta( $post->ID, 'wc_trendyolduct_price', true );
					$wc_trendyolduct_price = $wc_trendyolduct->content[0]->salePrice ?? $wc_trendyolduct_price;

					if ( $wc_trendyolduct_price != $wc_trendyolduct_price ) {
						$product_price = $wc_trendyolduct_price;
					} else {
						$product_price = $wc_trendyolduct_price;
					}
					//PRODUCT PRICE

					//PRODUCT BRAND
					$main_brand            = get_option( 'wc_trendyol_main_brand', null ) ?? '';
					$wc_trendyolduct_brand = get_post_meta( $post->ID, 'wc_trendyolduct_brand', true );
					if ( ! empty( $wc_trendyolduct_brand ) ) {
						$brand_explode = explode( ':', $wc_trendyolduct_brand );
					} else if ( ! empty( $main_brand ) ) {
						$brand_explode = explode( ':', $main_brand );
					}
					//PRODUCT BRAND

					//PRODUCT SHOW CUSTOMER QUESTIONS
					$wc_trendyol_show_customer_questions = get_post_meta( $post->ID, 'wc_trendyolduct_detail_show_customer_question', true );
					//PRODUCT SHOW CUSTOMER QUESTIONS

					//TRENDYOL COMMISSION
					$product_cat_id              = current( $cat );
					$get_trendyol_wc_category_id = get_term_meta( $product_cat_id, 'trendyol_wc_category_id', true );
					if ( ! empty( $get_trendyol_wc_category_id ) ) {
						$get_trendyol_cat_commission = $trendyol_adapter->get_category_info( $get_trendyol_wc_category_id );
					}
					//TRENDYOL COMMISSION

					//PRODUCT LOCKED CONTROL
					$product_locked         = $wc_trendyolduct->content[0]->locked ?? null;
					$product_locked_message = $wc_trendyolduct->content[0]->lockReason ?? null;
					//PRODUCT LOCKED CONTROL

					?>
                    <p class="wc_trendyolduct_price_content">

						<?php
							if ( $product_locked == 1 ) {
								?>
                                <span class="wc_trendyol_alert"><?=__( 'Ürününüz kitlenmiş sebebi', 'wc-trendyol' )?> :
                                    <strong><?=$product_locked_message?></strong>
                                </span>
								<?php
							}
						?>
                        <span class="wc_trendyol_title">
                            <img src="<?php echo plugin_dir_url( __DIR__ ) . '/admin/img/trendyol-logo.jpg'; ?>">
                            Trendyol Ayarları
                        </span>

                        <span class="wc_trendyol_settings wc_trendyol_col_3">
                            <label><?php _e( 'Trendyol Satış Fiyatı', 'wc-trendyol' ); ?> (₺)</label>
                            <input type="text" name="wc_trendyolduct_price" class="wc_trendyolduct_price_input" value="<?php echo $product_price ?? '' ?>" <?=$product_locked == 1 ? 'disabled' : ''?>>
                        </span>

                        <span class="wc_trendyol_settings wc_trendyol_col_3">
                            <label><?php _e( 'Trendyol Komisyonu', 'wc-trendyol' ); ?> (₺) (%<?php echo $get_trendyol_cat_commission->commission ?? '??' ?>)</label>
                            <input type="text" class="wc_trendyol_cat_commission_input" data-trendyol_cat_commission="<?php echo $get_trendyol_cat_commission->commission ?? 0 ?>" placeholder="<?php echo $get_trendyol_cat_commission->commission ?? __( 'Kategori Eşleştirilmemiş Komisyon Alınmadı' ) ?>" disabled>
                        </span>

                        <span class="wc_trendyol_settings wc_trendyol_col_3">
                            <label><?php _e( 'Trendyol Marka', 'wc-trendyol' ); ?></label>
                            <select name="wc_trendyolduct_brand" class="trendyol_select_2_search">
								<?php
									if ( isset( $brand_explode ) ) {
										?>
                                        <option value="<?=$brand_explode[0]?>:<?=$brand_explode[1]?>" selected><?=$brand_explode[1]?></option>
										<?php
									} else {
										?>
                                        <option value="">Marka Ara</option>
										<?php
									}
								?>

                            </select>
                        </span>

                    </p>
                    <div class="wc_trendyol_clear_both"></div>
					<?php

				} else if ( isset( $wc_trendyolduct->totalElements ) and $wc_trendyolduct->totalElements == 0 ) {
					?>
                    <p class="wc_trendyolduct_price_content">
                        <span class="wc_trendyol_alert">
                            <img src="<?php echo plugin_dir_url( __DIR__ ) . '/admin/img/trendyol-logo.jpg'; ?>">
                            Ürününüz Trendyolda bulunamadı. Ürününüze girdiğiniz SKU ile Trendyoldaki BARKOD kısmının aynı olmasına dikkat edin.
                        </span>
                    </p>
                    <div class="wc_trendyol_clear_both"></div>
					<?php
				} else {
					?>
                    <p class="wc_trendyolduct_price_content">
                        <span class="wc_trendyol_alert">
                            <img src="<?php echo plugin_dir_url( __DIR__ ) . '/admin/img/trendyol-logo.jpg'; ?>">
                            Trendyol ayarlarını yapılandırmadığınız için fiyat bilgisini güncelleyemiyorsunuz. Ayarlamak için
                            <a href="admin.php?page=trendyol_settings" target="_blank">Buraya Tıklayın</a>
                        </span>
                    </p>
                    <div class="wc_trendyol_clear_both"></div>
					<?php
				}
			} else {
				?>
                <p class="wc_trendyolduct_price_content">
                    <span class="wc_trendyol_alert">
                        <img src="<?php echo plugin_dir_url( __DIR__ ) . '/admin/img/trendyol-logo.jpg'; ?>">
                        Ürün SKU'su girilmediği için fiyat bilgisini göremiyorsunuz. Ürün SKU'su trendyoldaki barkod ile aynı olmalıdır.
                    </span>
                </p>
                <div class="wc_trendyol_clear_both"></div>
				<?php
			}

		}

		public function wc_trendyol_add_variation_product_price_field( $loop, $variation_data, $variation ): void {
			global $woocommerce, $post, $trendyol_adapter;

			$wc_product = wc_get_product( $post->ID );
			$sku        = $wc_product->get_sku();

			$trd_product = $trendyol_adapter->get_my_product( $sku );
			if ( $trd_product->totalElements != 0 ) {

				$wc_trendyol_price = get_post_meta( $post->ID, 'wc_trendyol_price', true );
				$trd_product_price = $trd_product->content[0]->salePrice;

				if ( $wc_trendyol_price != $trd_product_price ) {
					$product_price = $trd_product_price;
				} else {
					$product_price = $wc_trendyol_price;
				}

				echo '<div class="wc_trendyol_variation_price">';
				echo '<img src="' . plugin_dir_url( __DIR__ ) . '/admin/img/trendyol-logo.jpg">';
				woocommerce_wp_text_input( [
					'id'                => 'wc_trendyol_price[' . $loop . ']',
					'class'             => 'wc_trendyol_price wc_input_price',
					'placeholder'       => __( 'Ürünün Trendyol Satış Fiyatınızı Giriniz', 'woocommerce-trendyol' ),
					'label'             => __( 'Trendyol Satış Fiyatı', 'woocommerce-trendyol' ),
					'desc_tip'          => 'true',
					'type'              => 'number',
					'custom_attributes' => [
						'step' => 'any',
						'min'  => '0',
					],
					'value'             => $product_price,
				] );

				?>
                <span class="wrap">
                    <input id="product_real_length" placeholder="1" class="input-text wc_input_decimal" size="6" type="text" name="variable_real_length[1]" value="1"/>
                    <input id="product_real_width" placeholder="2" class="input-text wc_input_decimal" size="6" type="text" name="variable_real_width[2]" value="2"/>
                    <input id="product_real_height" placeholder="3" class="input-text wc_input_decimal last" size="6" type="text" name="variable_real_height[3]" value="3"/>
                </span>
				<?php

				echo '</div>';

			}
		}
		//ADD TRENDYOL PRICE FIELD

		//SAVE TRENDYOL PRICE
		public function wc_trendyol_save_basic_production_price_field( $post_id ) {
			global $trendyol_adapter;

			$wc_product                            = wc_get_product( $post_id );
			$sku                                   = $wc_product->get_sku();
			$wc_trendyolduct_trendyol_prodcut_code = get_post_meta( $post_id, 'wc_trendyol_trendyol_product_code', true );

			//TRENDYOL BARCODE SAVE
			if ( isset( $_POST['wc_trendyolduct_trendyol_barcode'] ) and ! empty( $_POST['wc_trendyolduct_trendyol_barcode'] ) ) {
				update_post_meta( $post_id, 'wc_trendyolduct_trendyol_barcode', sanitize_text_field( $_POST['wc_trendyolduct_trendyol_barcode'] ) );
			}
			//TRENDYOL BARCODE SAVE

			//TRENDYOL BRAND SAVE
			if ( isset( $_POST['wc_trendyolduct_brand'] ) and ! empty( $_POST['wc_trendyolduct_brand'] ) ) {
				$wc_trendyolduct_brand = sanitize_text_field( $_POST['wc_trendyolduct_brand'] );
				update_post_meta( $post_id, 'wc_trendyolduct_brand', $wc_trendyolduct_brand );

				$brand_explode = explode( ':', $wc_trendyolduct_brand );

				$wc_trendyolduct_info = $trendyol_adapter->get_my_product( $sku );

				$product_info = $wc_trendyolduct_info->content[0];

				$product_title  = $product_info->title;
				$product_desc   = $product_info->description;
				$product_images = $product_info->images;
				$product_stock  = $wc_product->get_price();
				$product_cat_id = $product_info->pimCategoryId;

				$trendyol_adapter->update_product( $sku, $product_title, $product_desc, $product_images, 18, $product_stock, $wc_trendyolduct_price, $brand_explode[0], $product_cat_id );
			}
			//TRENDYOL BRAND SAVE

			//TRENDYOL SALE PRICE SAVE
			$wc_trendyolduct_list_price_input  = sanitize_text_field( $_POST['wc_trendyolduct_list_price_input'] );
			$wc_trendyolduct_sales_price_input = sanitize_text_field( $_POST['wc_trendyolduct_sales_price_input'] );
			if ( isset( $_POST['wc_trendyolduct_list_price_input'], $_POST['wc_trendyolduct_sales_price_input'] ) and ! empty( $_POST['wc_trendyolduct_sales_price_input'] ) ) {
				$trendyol_adapter->update_product_price( $wc_trendyolduct_trendyol_prodcut_code, $wc_trendyolduct_list_price_input, $wc_trendyolduct_sales_price_input );
			}
			//TRENDYOL SALE PRICE SAVE

			//TRENDYOL PRODUCT DETAIL SHOW CUSTOMER QUESTION
			if ( isset( $_POST['wc_trendyol_show_customer_questions'] ) and ! empty( $_POST['wc_trendyol_show_customer_questions'] ) ) {
				update_post_meta( $post_id, 'wc_trendyol_show_customer_questions', 1 );
			} else {
				delete_post_meta( $post_id, 'wc_trendyol_show_customer_questions' );
			}
			//TRENDYOL PRODUCT DETAIL SHOW CUSTOMER QUESTION
		}

		public function wc_trendyol_save_variation_production_price_field( $variation_id, $i ): void {
			$wc_trendyol_price = sanitize_text_field( $_POST['wc_trendyol_price'][ $i ] );
			update_post_meta( $variation_id, 'wc_trendyol_price', esc_attr( $wc_trendyol_price ) );
		}
		//SAVE TRENDYOL PRICE

		//ADD WOOCOMMERCE PRODUCT CATEGORY COLUMN
		public function trendyol_add_wc_category_column( $columns ) {
			$columns['trendyol_category'] = __( 'Trendyol Kategorisi', 'wc-trendyol' );

			return $columns;
		}

		public function trendyol_add_wc_category_column_data( $columns, $column, $term_id ) {
			global $trendyol_adapter;

			if ( $column == 'trendyol_category' ) {

				$plugin_dir = plugin_dir_url( __DIR__ );

				$get_trendyol_wc_category_id = get_term_meta( $term_id, 'trendyol_wc_category_id', true );
				if ( ! empty( $get_trendyol_wc_category_id ) ) {
					$get_category_info = $trendyol_adapter->get_category_info( $get_trendyol_wc_category_id );

					return ( ( '<img src="' . $plugin_dir . 'admin/img/yes.png">' ) ) . ' ' . ( $get_category_info->displayName ?? 'Trendyoldan alınamadı' );
				} else {
					return ( ( '<img src="' . $plugin_dir . 'admin/img/no.png">' ) ) . ' ' . __( 'Trendyol ile eşitlenmemiş', 'wc-trendyol' );
				}
			}

			return $columns;
		}
		//ADD WOOCOMMERCE PRODUCT CATEGORY COLUMN

		//ADD WOOCOMMERCE PRODUCT CATEGORY FIELDS
		public function wc_trendyol_add_wc_category_field( $term ) {
			global $woocommerce, $post, $trendyol_adapter;

			wp_nonce_field( 'trendyol_pro_taxonomy_specific_nonce_data', 'trendyol_pro_taxonomy_specific_nonce' );

			if ( isset( $term->term_id ) ) {
				$get_trendyol_wc_category_id = get_term_meta( $term->term_id, 'trendyol_wc_category_id', true );
			}

			$trendyol_categories = $trendyol_adapter->get_all_categories();
			$convert_option      = $this->trendyol_categories_array_to_select_option( $trendyol_categories->categories, 0, ( $get_trendyol_wc_category_id ?? 0 ) );
			?>
            <tr>
                <th><?php
						_e( 'Trendyol Kategorisine Denk Gelen Kategori', 'wc-trendyol' ); ?></th>
                <td>
                    <select name="trendyol_category_id" id="trendyol_category_id" class="trendyol_select_2">
						<?php
							echo $convert_option; ?>
                    </select>
                </td>
            </tr>
			<?php
		}

		public function wc_trendyol_add_wc_category_field_save( $post_id ) {
			if ( ! wp_verify_nonce( $_POST['trendyol_pro_taxonomy_specific_nonce'], 'trendyol_pro_taxonomy_specific_nonce_data' ) ) {
				return $post_id;
			}

			if ( isset( $_POST['trendyol_category_id'] ) and ! empty( $_POST['trendyol_category_id'] ) ) {
				$trendyol_category_id = esc_attr( $_POST['trendyol_category_id'] );
				update_term_meta( $post_id, 'trendyol_wc_category_id', $trendyol_category_id );
			} else {
				delete_term_meta( $post_id, 'trendyol_wc_category_id' );
			}
		}
		//ADD WOOCOMMERCE PRODUCT CATEGORY FIELDS

		//ADD MENU
		public function trendyol_add_menu() {
			add_submenu_page( 'woocommerce', __( 'Trendyol Ayarları', 'wc-trendyol' ), __( 'Trendyol Ayarları', 'wc-trendyol' ), 'manage_woocommerce', 'trendyol_settings', [
				$this,
				'wc_trendyol_settings_page',
			] );
			add_submenu_page( 'edit.php?post_type=product', __( 'Trendyol Ürün Aktarma', 'wc-trendyol-pro' ), __( 'Trendyol Ürün Aktarma', 'wc-trendyol-pro' ), 'manage_woocommerce', 'trendyol_product_import_page', [
				$this,
				'wc_trendyol_product_import_page',
			] );
		}

		public function wc_trendyol_settings_page() {
			require ( __DIR__ ) . "/partials/wc-trendyol-admin-display.php";
		}

		public function wc_trendyol_welcome_page() {
			require ( __DIR__ ) . "/partials/wc-trendyol-welcome-page.php";
		}

		public function wc_trendyol_product_import_page() {
			require ( __DIR__ ) . "/partials/wc-trendyol-product-import-page.php";
		}
		//ADD MENU

		//FREE TRENDYOL CONTROL NOTICIATION
		function free_wc_trendyol_control_notice() {
			?>
            <div class="notice notice-error is-dismissible">
                <p><?php
						_e( 'Ücretsiz Trendyol eklentisi devre dışı bırakıldı. Eklentilerin çakışmaması için lütfen ücretsiz versiyonu silin.', 'wc-trendyol' ); ?></p>
            </div>
			<?php
		}
		//FREE TRENDYOL CONTROL NOTICIATION

		/**
		 * Register the stylesheets for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_styles() {

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

			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wc-trendyol-admin.css', [], $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name . '-select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', [], $this->version, 'all' );

		}

		/**
		 * Register the JavaScript for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_scripts() {

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

			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wc-trendyol-admin.js', [ 'jquery' ], $this->version, true );
			wp_enqueue_script( $this->plugin_name . '-select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', $this->version, true );

		}

		public function trendyol_categories_array_to_select_option( $categories = null, $depth = 0, $selected = 0 ) {

			$html = '';
			if ( $categories != null ) {
				foreach ( $categories as $id => $category ) {
					if ( isset( $category->subCategories ) and $category->subCategories != null ) {
						$html .= '<option value="' . $category->id . '" ' . ( $selected == $category->id ? 'selected' : '' ) . '>' . str_repeat( '-', ( $depth ) ) . $category->name . '</option>';
						$html .= $this->trendyol_categories_array_to_select_option( $category->subCategories, ( $depth + 1 ), $selected );
					} else {
						$html .= '<option value="' . $category->id . '" ' . ( $selected == $category->id ? 'selected' : '' ) . '>' . str_repeat( '-', ( $depth ) ) . $category->name . '</option>';
					}
				}
			}

			return $html;
		}
	}
