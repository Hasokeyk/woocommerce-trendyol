<?php

	global $trendyol_adapter;

	use Hasokeyk\Trendyol\Trendyol;

	//		delete_option('wc_trendyol_supplier');
	//		delete_option('wc_trendyol_username');
	//		delete_option('wc_trendyol_password');
	//			delete_option('wc_trendyol_main_brand');

	if(isset($_POST['post'])){

		if(isset($_POST['supplier_id'], $_POST['username'], $_POST['password'], $_POST['main_brand']) and !empty($_POST['supplier_id']) and !empty($_POST['username']) and !empty($_POST['password']) and !empty($_POST['main_brand'])){

			$supplier_id = sanitize_text_field($_POST['supplier_id']);
			$username    = sanitize_text_field($_POST['username']);
			$password    = sanitize_text_field($_POST['password']);
			$main_brand  = sanitize_text_field($_POST['main_brand']);

			$trendyol = new Trendyol($supplier_id, $username, $password);

			$products = $trendyol->marketplace->TrendyolMarketplaceProducts()->get_my_products();
			if((isset($products->exception) and $products->exception == 'TrendyolAuthorizationException') or (isset($products->status) and $products->status == '404')){

				delete_option('wc_trendyol_supplier');
				delete_option('wc_trendyol_username');
				delete_option('wc_trendyol_password');
				delete_option('wc_trendyol_main_brand');

				$result = [
					'status'  => 'error',
					'message' => __('Girdiğiniz bilgiler ile Trendyol\'a bağlanılamadı. Lütfen tekrar kontrol edin.', 'wc-trendyol'),
				];

			}
			else{

				update_option('wc_trendyol_supplier', $supplier_id);
				update_option('wc_trendyol_username', $username);
				update_option('wc_trendyol_password', $password);
				update_option('wc_trendyol_main_brand', $main_brand);

				$result = [
					'status'  => 'updated',
					'message' => __('Bilgiler geçerli. Kayıt yapılmıştır.', 'wc-trendyol'),
				];
			}

		}
		else{
			$result = [
				'status'  => 'error',
				'message' => __('Lütfen tüm alanları doldurunuz', 'wc-trendyol'),
			];
		}
	}

	$supplier_id = get_option('wc_trendyol_supplier', null) ?? '';
	$username    = get_option('wc_trendyol_username', null) ?? '';
	$password    = get_option('wc_trendyol_password', null) ?? '';
	$main_brand  = get_option('wc_trendyol_main_brand', null) ?? '';
?>
<div class="wrap">

	<?php
		if(isset($result) and $result != null){
			?>
            <div class="<?php esc_attr_e($result['status']); ?> vc_license-activation-notice" id="vc_license-activation-notice">
                <p><?php esc_attr_e($result['message']); ?></p>
                <button type="button" class="notice-dismiss vc-notice-dismiss">
                    <span class="screen-reader-text"><?php _e('Kapat', 'trendyol'); ?>>
                    </span>
                </button>
            </div>
			<?php
		}
	?>

    <h1><?php _e('Trendyol Ayarları', 'trendyol'); ?></h1>

    <form method="post" action="" novalidate="novalidate">
        <input type="hidden" name="post" value="true">
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="supplier_id"><?php _e('Trendyol Paneline Git', 'trendyol'); ?></label>
                </th>
                <td>
                    <p class="description" id="tagline-description">
                        <a href="https://partner.trendyol.com/account/info?tab=integrationInformation" target="_blank"><?php _e('Panele Gir', 'trendyol'); ?></a>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="supplier_id"><?php _e('Satıcı Id', 'trendyol'); ?></label>
                </th>
                <td>
                    <input name="supplier_id" type="text" id="supplier_id" value="<?php
						esc_attr_e($supplier_id); ?>" class="regular-text"/>
                    <p class="description" id="tagline-description"><?php _e('Trendyol panelindeki satıcı ID\'niz'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="username"><?php _e('API Key', 'trendyol'); ?></label>
                </th>
                <td>
                    <input name="username" type="text" id="username" value="<?php esc_attr_e($username); ?>" class="regular-text"/>
                    <p class="description" id="tagline-description"><?php _e('Trendyol panelindeki API Key'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="password"><?php _e('API Secret', 'trendyol'); ?></label>
                </th>
                <td>
                    <input name="password" type="text" id="password" value="<?php esc_attr_e($password); ?>" class="regular-text"/>
                    <p class="description" id="tagline-description"><?php _e('Trendyol panelindeki API Secret'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="password"><?php _e('Ana Marka', 'trendyol'); ?></label>
                </th>
                <td>
                    <select name="main_brand" class="trendyol_select_2_search" style="width: 25em">
                        <option value="">Ana Marka Ara</option>
						<?php
							if(!empty($main_brand)){
                                $brand_explode = explode(':',$main_brand);
								?>
                                <option value="<?=$brand_explode[0]?>" selected><?=$brand_explode[1]?></option>
								<?php
							}
						?>
                    </select>
                    <p class="description" id="tagline-description"><?php _e('Trendyol panelinde size ait markayı seçiniz'); ?></p>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Değişiklikleri kaydet'); ?>">
        </p>
    </form>
</div>
