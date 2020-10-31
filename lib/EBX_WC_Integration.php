<?php

/**
 * Integration EuroBlox Connect.
 *
 * @package  WC_Integration_EuroBlox_Integration
 * @category Integration
 * @author   Galatan Ovidiu Iulian
 */
if ( class_exists( 'WC_Integration' ) ) {
	class EBX_WC_Integration extends WC_Integration {
		/**
		 * Init and hook in the integration.
		 */
		public function __construct() {
//		global $woocommerce;
			$this->id                 = 'euroblox_connect';
			$this->method_title       = __( 'EuroBlox Connect', 'woocommerce-integration-demo' );
			$this->method_description = __( 'Connect to EuroBlox Marketplace.', 'woocommerce-integration-demo' );
			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();
			// Define user set variables.
			$this->api_key = $this->get_option( 'euroblox_api_key' );
			$this->debug   = $this->get_option( 'euroblox_debug' );
			// Actions.
			add_action( 'woocommerce_update_options_integration_' . $this->id, [ $this, 'process_admin_options' ] );
		}

		/**
		 * Initialize integration settings form fields.
		 */
		public function init_form_fields() {
			$this->form_fields = [
				'euroblox_api_key'   => [
					'title'       => __( 'API Key', 'woocommerce-integration-demo' ),
					'type'        => 'text',
					'description' => __( 'Enter with your API Key. You can find this in "User Profile" drop-down (top right corner) > API Keys.', 'woocommerce-integration-demo' ),
					'desc_tip'    => false,
					'default'     => '',
				],
				'euroblox_shipping_payment'   => [
					'title'       => __( 'Shipping & Payment Info', 'woocommerce-integration-demo' ),
					'type'        => 'textarea',
					'description' => __( 'This information will be displayed on EuroBlox under product description', 'woocommerce-integration-demo' ),
					'desc_tip'    => false,
					'default'     => '',
				],
//                'euroblox_debug' => array(
//                    'title'             => __( 'Debug Log', 'woocommerce-integration-demo' ),
//                    'type'              => 'checkbox',
//                    'label'             => __( 'Enable logging', 'woocommerce-integration-demo' ),
//                    'default'           => 'no',
//                    'description'       => __( 'Log events such as API requests', 'woocommerce-integration-demo' ),
//                ),
				'integration_active' => [
					'title'       => __( 'Activate Integration', 'woocommerce-integration-demo' ),
					'type'        => 'integration_active',
					'description' => __( 'Customize your settings by going to the integration site directly.', 'woocommerce-integration-demo' ),
//                    'custom_attributes' => array(
//                        'onclick' => "location.href='http://www.woothemes.com'",
//                    ),
					'desc_tip'    => false,
				],
			];
		}

		public function generate_integration_active_html( $key, $data ) {
			$field    = $this->plugin_id . $this->id . '_' . $key;
			$defaults = [
				'class'             => 'button-secondary',
				'css'               => '',
				'custom_attributes' => [],
				'desc_tip'          => false,
				'description'       => '',
				'title'             => '',
			];

			$euroblox_connect_settings = get_option( 'woocommerce_euroblox_connect_settings' );

			$data = wp_parse_args( $data, $defaults );

			ob_start();
			?>
          <tr valign="top" id="products_top">
            <th scope="row" class="titledesc">
              <label for="<?php echo esc_attr( $field ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
				<?php echo $this->get_tooltip_html( $data ); ?>
            </th>
            <td class="forminp">

				<?php if ( $euroblox_connect_settings['euroblox_api_key'] ) { ?>

          <?php echo get_locale(); ?>
          <?php echo get_user_locale(); ?>

                  <fieldset>
                    <legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
                    <button class="<?php echo esc_attr( $data['class'] ); ?>" type="button" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" <?php echo $this->get_custom_attribute_html( $data ); ?> onclick="ebx_activate_integration()"><?php echo wp_kses_post( $data['title'] ); ?></button>
                    <!--                        --><?php //echo $this->get_description_html( $data ); ?>
                  </fieldset>
                  <div id="ebx_text_integration"></div>
                  <!--                    --<pre>--><?php //print_r($euroblox_connect_settings);; ?><!--</pre>---->


                  <script>
                    var ebx_test_integration = jQuery('#ebx_text_integration')

                    function ebx_activate_integration() {
                      // test integration
                      ebx_test_integration.html('Testing Integration...')
                      jQuery.ajax('https://euroblox-api.ovi.work/api/test_integration?api_key=<?php echo $euroblox_connect_settings['euroblox_api_key']; ?>', {
                        method: 'get',
                      }).done(function (data) {
                        var integration_ok = data.test_integration_ok
                        if (integration_ok) {
                          ebx_test_integration.html('Integration test OK')
                        } else {
                          ebx_test_integration.html('Your API key is invalid')
                        }
                      })
                    }
                  </script>
				<?php } ?>

            </td>
          </tr>
          <tr valign="top">
            <th scope="row" class="titledesc">
              <label for="">Connected Products</label>
				<?php echo $this->get_tooltip_html( $data ); ?>
            </th>
            <td class="forminp">
              <div id="ebx_app" style="position:relative;">
                <div v-if="loading_data" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); color: #fff; text-align: center; padding-top: 40px"> Loading... </div>
                <table class="wp-list-table widefat fixed striped table-view-list pages">
                  <tbody>
                  <tr v-for="(p, index) in products">
                    <td style="width: 30px">
                      <span class="spinner is-active" v-if="p.activating"></span>
                      <input v-else type="checkbox" v-model="p.active" :disabled="!p.category" @change="set_active_state(p.id, index, p.active)">
                    </td>
                    <td><a :href="'/wp-admin/post.php?post='+ p.id +'&action=edit'" target="edit_product">{{p.name}}</a></td>
                    <td>{{cat_names[p.category]}}</td>
                  </tr>
                  </tbody>
                </table>
                <div style="margin-top: 15px">
                  <span v-for="pg in total_pages" class="ebx_page" :class="{ebx_page_active: page === pg}" @click="setPage(pg)">{{pg}}</span>
                </div>
              </div>
              <style>
                .ebx_page {
                  display: inline-block;
                  margin-right: 5px;
                  margin-bottom: 5px;
                  padding: 0 5px;
                  background-color: #fff;
                  cursor: pointer;
                  border: 1px solid #ccc
                }
                .ebx_page.ebx_page_active {
                  background-color: #007cba;
                  color: #fff;
                }
              </style>
              <script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
              <script>
                ///wp-admin/admin-ajax.php?action=get_euroblox_products&page=3
                var ebx_app = new Vue({
                  el: '#ebx_app',
                  data: {
                    products: [],
                    page: 1,
                    total_pages: 1,
                    ebx_categories: [],
                    loading_data: false
                  },
                  computed: {
                    cat_names() {
                      var categories = {}
                      for (var i = 0; i < this.ebx_categories.length; i++) {
                        categories[this.ebx_categories[i]._id] = this.ebx_categories[i].full_n
                      }
                      return categories
                    },
                  },
                  mounted() {
                    var self = this
                    self.get_products()
                    self.get_categories()
                  },
                  methods: {
                    get_products() {
                      var self = this
                      this.loading_data = true
                      document.location = '#woocommerce_euroblox_connect_euroblox_api_key'
                      jQuery.ajax('/wp-admin/admin-ajax.php?action=get_euroblox_products&page=' + self.page).done(function (data) {
                        self.products = data.data
                        self.total_pages = data.max_num_pages
                        self.loading_data = false
                      })
                    },
                    setPage(page){
                      this.page = page;
                      this.get_products();
                    },
                    get_categories() {
                      var self = this
                      jQuery.ajax('https://euroblox-api.ovi.work/api/ebx_data/categories_all_flat?lang=<?php echo get_user_locale() ?: 'en'; ?>').done(function (data) {
                        self.ebx_categories = data
                      })
                    },
                    set_active_state(product_id, index, active) {
                      this.products[index].activating = true
                      var self = this
                      jQuery.ajax('/wp-admin/admin-ajax.php', {
                        method: 'post',
                        data: {
                          action: 'ebx_set_product_active',
                          product_id: product_id,
                          active: active
                        }
                      }).done(function (data) {
                          self.products[index].activating = false
                      })
                    },
                  },
                })
              </script>

            </td>
          </tr>
			<?php
			return ob_get_clean();
		}
	}
}

