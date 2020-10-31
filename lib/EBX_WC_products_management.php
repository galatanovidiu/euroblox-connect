<?php


class EBX_WC_products_management {
	public function __construct() {
		add_action( 'wp_ajax_get_euroblox_products', [ $this, 'get_euroblox_products' ] );
		add_action( 'wp_ajax_ebx_set_product_active', [ $this, 'ebx_set_product_active' ] );
		add_action( 'wp_ajax_ebx_products_list', [ $this, 'ebx_products_list' ] );
		add_action( 'wp_ajax_nopriv_ebx_products_list', [ $this, 'ebx_products_list' ] );
		add_filter( 'woocommerce_product_data_store_cpt_get_products_query', [ $this, '_handle_custom_query_var' ], 9, 2 );
	}

	public function get_euroblox_products() {

		$products = wc_get_products( [
			                             'stock_status' => 'instock',
			                             'paginate'     => true,
			                             'limit'        => 30,
			                             'page'         => $_GET['page'] ? $_GET['page'] : 1,
		                             ] );

		$return = [];
//		print_r( $products );
		foreach ( $products->products as $product ) {
//			print_r($product);
//			$product_data = new WC_Product( $product->ID );
//			$attributes   = $product_data->get_attributes();

//			$product_variations = new WC_Product_Variable( $product->ID );
//			$product_variations = $product_variations->get_available_variations();


			$category = get_post_meta( $product->id, '_ebx_category', true );

			if ( ! $category ) {
				$product_categories = wp_get_post_terms( $product->id, 'product_cat' );

				foreach ( $product_categories as $product_category ) {
					$category = get_term_meta( $product_category->term_id, 'ebx_category_connect', true );
					if ( $category ) {
						break;
					}
				}
			}

			$return[] = [
				'id'          => $product->id,
				'name'        => $product->name,
				'description' => apply_filters( 'the_content', $product->description ),
				'category'    => $category,
				'active'      => get_post_meta( $product->id, '_ebx_visible_to_marketplace', true ) == 'yes' ?  true : false,
				'attributes'  => $product->attributes,
//				'variations'  => $product_variations,
				'activating'  => false,
			];
		}

		wp_send_json( [ 'max_num_pages' => $products->max_num_pages, 'current_page' => $products->query['paged'], 'data' => $return ] );

	}

	public function ebx_set_product_active() {

		update_post_meta( $_POST['product_id'], '_ebx_visible_to_marketplace', $_POST['active'] === 'true' ? 'yes' : 'no' );

		wp_send_json( [ $_POST['product_id'] => 'product_id', 'active' => $_POST['active'] ] );

	}

	public function ebx_products_list() {
		$products = wc_get_products( [
			                             'stock_status'               => 'instock',
			                             'paginate'                   => true,
			                             'limit'                      => 300,
			                             'page'                       => $_GET['page'] ? $_GET['page'] : 1,
			                             'ebx_visible_to_marketplace' => 'yes',
		                             ] );

		$euroblox_connect_settings = get_option( 'woocommerce_euroblox_connect_settings' );
		$return = [];
		foreach ( $products->products as $product ) {

			$product_variations = new WC_Product_Variable( $product->id );

			$category = get_post_meta( $product->id, '_ebx_category', true );

			if ( ! $category ) {
				$product_categories = wp_get_post_terms( $product->id, 'product_cat' );

				foreach ( $product_categories as $product_category ) {
					$category = get_term_meta( $product_category->term_id, 'ebx_category_connect', true );
					if ( $category ) {
						break;
					}
				}
			}

			$images_ids                                        = [];
			$images_ids[ $product->get_image_id() ]            = 1;
			$images_ids[ $product_variations->get_image_id() ] = 1;
			foreach ( $product->get_gallery_image_ids() as $gallery_image_id ) {
				$images_ids[ $gallery_image_id ] = 1;
			}
			foreach ( $product_variations->get_gallery_image_ids() as $gallery_image_id ) {
				$images_ids[ $gallery_image_id ] = 1;
			}
			$images_ids = array_keys( $images_ids );
			$images     = [];
			foreach ( $images_ids as $images_id ) {
				$images[] = wp_get_attachment_image_src( $images_id, 'full' )[0];
			}

			$return[] = [
				'id'          => $product->id,
				'name'        => $product->name,
				'description' => apply_filters( 'the_content', $product->description ),
				'category'    => $category,
				'active'      => get_post_meta( $product->id, '_ebx_visible_to_marketplace', true ) == 'yes' ?  true : false,
				'attributes'  => $product->attributes,
				'variations'  => $product_variations->get_available_variations(),
				'price'       => $product->get_price(),
				'currency'    => get_woocommerce_currency(),
				'link'        => get_the_permalink( $product->id ),
				'images'      => $images,
				'shipping'      => $euroblox_connect_settings[''],
			];
		}

		wp_send_json( $return );

	}

	public function _handle_custom_query_var( $query, $query_vars ) {
		if ( ! empty( $query_vars['ebx_visible_to_marketplace'] ) ) {
			$query['meta_query'][] = [
				'key'   => '_ebx_visible_to_marketplace',
				'value' => esc_attr( $query_vars['ebx_visible_to_marketplace'] ),
			];
		}
//		print_r($query);
//		print_r($query_vars);

		return $query;
	}

}

new EBX_WC_products_management();
