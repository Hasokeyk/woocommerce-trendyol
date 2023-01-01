<?php

	namespace WoocommerceTrendyolAdapter;

	use Hasokeyk\Trendyol\Trendyol;
	use IS\PazarYeri\Trendyol\TrendyolClient;
	use IS\PazarYeri\Trendyol\Helper\TrendyolException;

	class woocommerce_trendyol_adapter{

		private $trendyol;

		public function __construct(){

			$supplier_id = get_option('wc_trendyol_supplier', null);
			$username    = get_option('wc_trendyol_username', null);
			$password    = get_option('wc_trendyol_password', null);

			$trendyol = new Trendyol($supplier_id, $username, $password);

			$this->trendyol = $trendyol->TrendyolMarketplace();

		}

		public function get_brands(){
			$trendyol_brands = $this->trendyol->TrendyolMarketplaceBrands();
			$brands          = $trendyol_brands->get_brands();

			return $brands;
		}

		public function search_brand($brand_name = ''){
			$trendyol_brands = $this->trendyol->TrendyolMarketplaceBrands();
			$brands          = $trendyol_brands->search_brand($brand_name);

			return $brands;
		}

		public function get_my_all_products(){
			$trendyol_products = $this->trendyol->TrendyolMarketplaceProducts();
			$products          = $trendyol_products->get_my_products();

			return $products;
		}

		public function get_my_product($barcode = ''){
			$trendyol_products = $this->trendyol->TrendyolMarketplaceProducts();
			$products          = $trendyol_products->get_my_products([
				'barcode' => $barcode,
			]);

			return $products;
		}

		public function update_product_price($barcode = '', $list_price = null, $sales_price = null){
			$trendyol_products = $this->trendyol->TrendyolMarketplaceProducts();
			$update_price      = $trendyol_products->update_product_price_and_stock($barcode, null, $sales_price, $list_price);

			return $update_price;
		}

		public function update_product($barcode = null, $title = null, $description = null, $image = null, $vat_rate = 18, $quantity = 1, $price = null, $brand_id = null, $category_id = null){
			$trendyol_product = $this->trendyol->TrendyolMarketplaceProducts();
			$update_product   = $trendyol_product->update_product_info($barcode, [
				'title'              => $title,
				'productMainId'      => $barcode,
				'brandId'            => $brand_id,
				'categoryId'         => $category_id,
				'quantity'           => $quantity,
				'stockCode'          => $barcode,
				'dimensionalWeight'  => 1,
				'description'        => $description,
				'currencyType'       => 'TRY',
				'listPrice'          => $price,
				'salePrice'          => $price,
				'cargoCompanyId'     => 1,
				'deliveryDuration'   => 2,
				'images'             => $image,
				'vatRate'            => $vat_rate,
				'shipmentAddressId'  => 1,
				'returningAddressId' => 1,
				'attributes'         => null,
			]);

			return $update_product;
		}

		public function add_product($barcode = null, $title = null, $description = null, $image = null, $vat_rate = 18, $quantity = 1, $price = null, $brand_id = null, $category_id = null){
			$trendyol_product = $this->trendyol->TrendyolMarketplaceProducts();
			$add_product      = $trendyol_product->create_product([
				'items' => [
					'barcode'            => $barcode,
					'title'              => $title,
					'productMainId'      => $barcode,
					'brandId'            => $brand_id,
					'categoryId'         => $category_id,
					'quantity'           => $quantity,
					'stockCode'          => $barcode,
					'dimensionalWeight'  => 1,
					'description'        => $description,
					'currencyType'       => 'TRY',
					'listPrice'          => $price,
					'salePrice'          => $price,
					'cargoCompanyId'     => 1,
					'deliveryDuration'   => 2,
					'images'             => [
						$image,
					],
					'vatRate'            => $vat_rate,
					'shipmentAddressId'  => 1,
					'returningAddressId' => 1,
					'attributes'         => null,
				],
			]);

			return $add_product;
		}

		public function get_all_categories(){
			$trendyol_categories = $this->trendyol->TrendyolMarketplaceCategories();
			$categories          = $trendyol_categories->get_categories();

			return $categories;
		}

		public function get_category_info($category_id = 0){
			$trendyol_categories = $this->trendyol->TrendyolMarketplaceCategories();
			$categories          = $trendyol_categories->get_category_info($category_id);

			return $categories;
		}

		public function get_product_questions($sku = null){
			$trendyol_question = $this->trendyol->TrendyolMarketplaceCustomerQuestions();
			$product_question  = $trendyol_question->get_product_question_web($sku);
			return $product_question;
		}
	}

	global $trendyol_adapter;

	$trendyol_adapter = new woocommerce_trendyol_adapter();