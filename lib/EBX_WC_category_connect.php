<?php


class EBX_WC_category_connect
{
    public function __construct()
    {
        add_action('product_cat_add_form_fields', [$this, 'ebx_add_category_fields'], 10);
        add_action('product_cat_edit_form_fields', [$this, 'ebx_add_category_fields'], 10);
        add_action('created_term', [$this, 'save_category_fields'], 10, 3);
        add_action('edit_term', [$this, 'save_category_fields'], 10, 3);
    }

    public function ebx_add_category_fields($cat)
    {
        ?>
      <script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
        <?php
        $ebx_category = '';
        if (is_object($cat)) {
            $ebx_category = get_term_meta($cat->term_id, 'ebx_category_connect', true);
            ?>
          <tr id="ebx_app" style="display: none" class="form-field term-thumbnail-wrap">
            <th scope="row" valign="top"><label>EuroBlox Category</label></th>
            <td>
              <select name="ebx_category_connect" id="_ebx_category" v-model="ebx_category" style="display: block; width: 100%">
                <option v-for="c in ebx_categories" :value="c._id" :disabled="!c.is_last">{{' &nbsp; &mdash; '.repeat(c.level)}} {{c.full_n}}</option>
              </select>
              <div v-if="selected_category_display[0] && selected_category_display[0].full_n">
                <b>
                  {{selected_category_display[0].full_n}}
                </b>
              </div>
            </td>
          </tr>
            <?php
        } else {
            ?>
          <div id="ebx_app" style="display: none" class="form-field term-thumbnail-wrap">
            <label for="_ebx_category"><label>EuroBlox Connected Category</label></label>
            <div>
              <select name="ebx_category_connect" id="_ebx_category" v-model="ebx_category" style="display: block; width: 100%">
                <option v-for="c in ebx_categories" :value="c._id" :disabled="!c.is_last">{{' &nbsp; &mdash; '.repeat(c.level)}} {{c.full_n}}</option>
              </select>
              <div v-if="selected_category_display[0] && selected_category_display[0].full_n">
                <b>
                  {{selected_category_display[0].full_n}}
                </b>
              </div>
            </div>
          </div>
            <?php
        }

        ?>
      <script>
        jQuery(document).ready(function () {
          var ebx_app = new Vue({
            el: '#ebx_app',
            data: {
              ebx_category: '<?php echo $ebx_category; ?>',
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
              jQuery('#ebx_app').show()
              var self = this
              setTimeout(function () {
                self.get_categories()

                jQuery('#_ebx_category').select2()

              }, 100)
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
        })
      </script>
        <?php

    }

    public function save_category_fields($term_id, $tt_id = '', $taxonomy = '')
    {
//	  die("SAVING CATEGORY {$_POST['ebx_category_connect']} {$taxonomy}");
        if (isset($_POST['ebx_category_connect']) && 'product_cat'===$taxonomy) { // WPCS: CSRF ok, input var ok.
            update_term_meta($term_id, 'ebx_category_connect', esc_attr($_POST['ebx_category_connect'])); // WPCS: CSRF ok, sanitization ok, input var ok.
        }
    }
}

$EBX_WC_category_connect = new EBX_WC_category_connect();


