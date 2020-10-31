<?php


class EBX_WC_products_admin {
	public function __construct() {
		add_filter( 'woocommerce_product_data_tabs', [ $this, 'euroblox_product_tab' ] );
		add_action( 'woocommerce_product_data_panels', [ $this, 'woo_euroblox_tab_content' ] );
		add_action( 'woocommerce_process_product_meta', [ $this, 'woocommerce_process_product_meta_callback' ] );

	}

	public function euroblox_product_tab( $tabs ) {

		$tabs['euroblox_tab'] = [
			'label'  => __( 'EuroBlox Marketplace', 'woocommerce' ),
			'target' => 'woo_euroblox_tab_content',
		];

		return $tabs;

	}

	public function woo_euroblox_tab_content() {

		global $post;

		$category = get_post_meta( $post->ID, '_ebx_category', true );

		if ( ! $category ) {
			$product_categories = wp_get_post_terms( $post->ID, 'product_cat' );

			foreach ( $product_categories as $product_category ) {
				$category = get_term_meta( $product_category->term_id, 'ebx_category_connect', true );
				if ( $category ) {
					break;
				}
			}
		}

		?>
		<div id="woo_euroblox_tab_content" style="padding: 15px" class="panel woocommerce_options_panel">
			<h1>EuroBlox Marketplace</h1>
			<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>

			<div id="ebx_app">
				<?php
				woocommerce_wp_checkbox( [
					                         'id'            => '_ebx_visible_to_marketplace',
					                         'wrapper_class' => 'show_if_simple show_if_variable',
					                         'label'         => __( 'Visible To Marketplace' ),
					                         'description'   => __( 'Is product visible to EuroBlox Marketplace?' ),
					                         'default'       => '0',
					                         'desc_tip'      => false,
				                         ] );

				?>


				<div class="_ebx_category_field" style="display: flex;">
					<label for="_ebx_category" style="float: none; margin: 0; padding-left: 10px;">EuroBlox Category</label>
					<div>

						<p>You can <a href="/wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product">connect your products categories to euroBloX categories</a> or you can set the category for this product below </p>

						<select name="_ebx_category" id="_ebx_category" v-model="ebx_category" style="display: block; width: 100%">
							<option v-for="c in ebx_categories" :value="c._id" :disabled="!c.is_last">{{' &nbsp; &mdash; '.repeat(c.level)}} {{c.full_n}}</option>
						</select>
						<div v-if="selected_category_display[0] && selected_category_display[0].full_n">
							<b>
								{{selected_category_display[0].full_n}}
							</b>
						</div>
					</div>
				</div>


			</div>
			<style type="text/css">
              .ebx_scd .ebx_bc:after {
                content: ' / ';
              }

              .ebx_scd .ebx_bc:last-child:after {
                content: '';
              }
			</style>
			<script>
              var ebx_app = new Vue({
                el: '#ebx_app',
                data: {
                  ebx_category: '<?php echo $category; ?>',
                  ebx_categories: [],
                },
                computed: {
                  selected_category_display() {
                    var self = this
                    return _.filter(this.ebx_categories, function (o) {
                      return o._id === self.ebx_category
                    })
                  },
                },
                mounted() {
                  var self = this
                  self.get_categories()
                  jQuery('#_ebx_category').select2()
                },
                methods: {
                  get_categories() {
                    var self = this
                    jQuery.ajax('https://euroblox-api.ovi.work/api/ebx_data/categories_all_flat?lang=<?php echo get_user_locale() ?: 'en'; ?>').done(function (data) {
                      self.ebx_categories = data
                      console.log(self.ebx_categories)
                    })
                  },
                },
              })
			</script>
		</div>
		<?php
	}

	public function woocommerce_process_product_meta_callback( $post_id ) {
		$fields = [
			'_ebx_visible_to_marketplace' => 'checkbox',
			'_ebx_category'               => 'text',
		];

		$product_categories = wp_get_post_terms( $post_id, 'product_cat' );

		foreach ( $product_categories as $product_category ) {
			$category = get_term_meta( $product_category->term_id, 'ebx_category_connect', true );
			if ( $category ) {
				break;
			}
		}

		foreach ( $fields as $field => $type ) {
			if ( $type == 'checkbox' ) {
				$value = array_key_exists( $field, $_POST ) ? 'yes' : 'no';
			} else {
				$value = array_key_exists( $field, $_POST ) ? esc_html( $_POST[ $field ] ) : null;
			}

			if ( $field == '_ebx_category' ) {
				if ( $category != $_POST[ $field ] ) {
					update_post_meta( $post_id, $field, $value );
				} else {
					delete_post_meta( $post_id, $field );
				}
			} else {
				update_post_meta( $post_id, $field, $value );
			}

		}
	}
}

$EBX_WC_products_admin = new EBX_WC_products_admin();
