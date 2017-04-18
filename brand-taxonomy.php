
<?php
/**
* @wordpress-plugin
* Plugin Name: beauty-product-brand-taxonomy
* Plugin URI: http://github.com/shortlist-digital/beauty-product-brand-taxonomy
* Description: Add a beauty product brand to a product post
* Version: 1.0.0
* Author: Shortlist Studio
* Author URI: http://shortlist.studio
* License: MIT
*/
class BeautyProductBrandTaxonomy
{
	public function __construct()
	{
		add_action('init', array($this, 'register_custom_taxonomy'));
		add_filter('timber_context', array($this, 'add_beauty_product_brand_to_context'), 10, 3);
		add_filter('admin_menu', array($this, 'remove_beauty_product_brand_box'), 10, 1);
		add_Filter('init', array($this, 'add_nice_beauty_product_brand_selector'), 10, 2);
		add_action('wp_head', array($this, 'create_beauty_product_brand_reference'));
	}
	private function get_beauty_product_brand() {
		global $post;
		if (!empty($post)) {
	   		return get_the_terms($post->ID, 'beauty_product_brand')[0];
		} else {
			return null;
		}
	}
	public function remove_beauty_product_brand_box()
	{
		remove_meta_box('tagsdiv-beauty_product_brand', 'product', 'normal');
	}
	public function add_nice_beauty_product_brand_selector()
	{
		acf_add_local_field_group(array (
			'key' => 'group_beauty_product_brand',
			'title' => 'Beauty Product Brand',
			'fields' => array (
				array (
					'key' => 'product_beauty_product_brand',
					'label' => 'Beauty Product Brand',
					'name' => 'beauty_product_brand',
					'type' => 'taxonomy',
					'instructions' => 'Select a beauty product brand for this content',
					'required' => 1,
					'taxonomy' => 'beauty_product_brand',
					'field_type' => 'select',
					'allow_null' => 0,
					'add_term' => 0,
					'save_terms' => 1,
					'load_terms' => 1,
					'return_format' => 'object',
					'label_placement' => 'top',
					'instruction_placement' => 'label',
					'active' => 1,
					'description' => 'Select a beauty product brand for this content',
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'product',
					),
				),
			),
		));
	}
	public function apply_acf_to_beauty_product_brand($acf_fields)
	{
		array_push($acf_fields['beauty_product_brand'], array(
			array(
				'param' => 'taxonomy',
				'operator' => '==',
				'value' => 'beauty_product_brand',
			),
		));
		return $acf_fields;
	}
	public function add_beauty_product_brand_to_context($context)
	{
		global $post;
		if ($post) {
			$context['beauty_product_brands'] = $this->get_beauty_product_brand();
		}
		return $context;
	}

	// Register Custom Taxonomy
	public function register_custom_taxonomy()
	{
		$labels = array(
			'name'                       => _x( 'Beauty Product Brand', 'Taxonomy General Name', 'text_domain' ),
			'singular_name'              => _x( 'Beauty Product Brand', 'Taxonomy Singular Name', 'text_domain' ),
			'menu_name'                  => __( 'Beauty Product Brand', 'text_domain' ),
			'all_items'                  => __( 'All beauty product brands', 'text_domain' ),
			'parent_item'                => __( 'Parent beauty product brand', 'text_domain' ),
			'parent_item_colon'          => __( 'Parent beauty product brand:', 'text_domain' ),
			'new_item_name'              => __( 'New beauty product brand', 'text_domain' ),
			'add_new_item'               => __( 'Add beauty product brand', 'text_domain' ),
			'edit_item'                  => __( 'Edit beauty product brand', 'text_domain' ),
			'update_item'                => __( 'Update beauty product brand', 'text_domain' ),
			'view_item'                  => __( 'View beauty product brand', 'text_domain' ),
			'separate_items_with_commas' => __( 'Separate beauty product brands with commas', 'text_domain' ),
			'add_or_remove_items'        => __( 'Add or remove beauty product brands', 'text_domain' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
			'popular_items'              => __( 'Popular beauty product brands', 'text_domain' ),
			'search_items'               => __( 'Search beauty product brands', 'text_domain' ),
			'not_found'                  => __( 'Not Found', 'text_domain' ),
			'no_terms'                   => __( 'No beauty product brands', 'text_domain' ),
			'items_list'                 => __( 'Beauty Product Brands list', 'text_domain' ),
			'items_list_navigation'      => __( 'Beauty Product Brands list navigation', 'text_domain' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => false,
			'rewrite'                      => array(
				// 'slug' => '/',
				'with_front' => false
			),
			'show_in_rest'       => true,
			'rest_base'          => 'beauty_product_brands',
			'rest_controller_class' => 'WP_REST_Terms_Controller',
		);
		register_taxonomy('beauty_product_brand', array( 'product' ), $args);
	}
	public function create_beauty_product_brand_reference() {
		$beauty_product_brand_object = json_encode($this->get_beauty_product_brand());
		echo "<script>window.agreableBeautyProductBrand = " . $beauty_product_brand_object . "</script>";
	}
}
new BeautyProductBrandTaxonomy();
