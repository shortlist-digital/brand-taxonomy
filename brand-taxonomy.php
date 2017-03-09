
<?php
/**
* @wordpress-plugin
* Plugin Name: brand-taxonomy
* Plugin URI: http://github.com/shortlist-digital/brand-taxonomy
* Description: Add a brand to a product post
* Version: 1.0.0
* Author: Shortlist Studio
* Author URI: http://shortlist.studio
* License: MIT
*/
class BrandTaxonomy
{
	public function __construct()
	{
		add_action('init', array($this, 'register_custom_taxonomy'));
		add_filter('timber_context', array($this, 'add_brand_to_context'), 10, 3);
		add_filter('agreable_base_theme_article_basic_acf', array($this, 'apply_acf_to_brand'), 10, 1);
		add_filter('agreable_base_theme_category_widgets_acf', array($this, 'apply_acf_to_brand'), 10, 1);
		add_filter('agreable_base_theme_social_media_acf', array($this, 'apply_acf_to_brand'), 10, 1);
		add_filter('agreable_base_theme_html_overrides_acf', array($this, 'apply_acf_to_brand'), 10, 1);
		add_filter('admin_menu', array($this, 'remove_brand_box'), 10, 1);
		add_Filter('agreable_base_theme_article_basic_acf', array($this, 'add_nice_brand_selector'), 10, 2);
		add_action('wp_head', array($this, 'create_brand_reference'));
	}
	private function get_brand() {
		global $post;
		if (!empty($post)) {
	   		return get_the_terms($post->ID, 'brand')[0];
		} else {
			return null;
		}
	}
	public function remove_brand_box()
	{
		remove_meta_box('tagsdiv-brand', 'product', 'normal');
	}
	public function add_nice_brand_selector()
	{
		acf_add_local_field_group(array (
			'key' => 'group_brand',
			'title' => 'Brand',
			'fields' => array (
				array (
					'key' => 'product_brand',
					'label' => 'Brand',
					'name' => 'brand',
					'type' => 'taxonomy',
					'instructions' => 'Select a brand for this content',
					'required' => 1,
					'taxonomy' => 'brand',
					'field_type' => 'select',
					'allow_null' => 0,
					'add_term' => 0,
					'save_terms' => 1,
					'load_terms' => 1,
					'return_format' => 'object',
					'label_placement' => 'top',
					'instruction_placement' => 'label',
					'active' => 1,
					'description' => 'Select a brand for this content',
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
	public function apply_acf_to_brand($acf_fields)
	{
		array_push($acf_fields['brand'], array(
			array(
				'param' => 'taxonomy',
				'operator' => '==',
				'value' => 'brand',
			),
		));
		return $acf_fields;
	}
	public function add_brand_to_context($context)
	{
		global $post;
		if ($post) {
			$context['brands'] = $this->get_brand();
		}
		return $context;
	}
	public function brand_permalink($permalink, $post_id, $leavename)
	{
		// No brand in the permalink so bail out
		if (strpos($permalink, '%brand%') === false) {
			return $permalink;
		}
		// If no post is returned for some reason bail out
		$post = get_post($post_id);
		if (!$post) {
			return $permalink;
		}
		// Tryr and get value of the 'brand' field
		$terms = wp_get_object_terms($post->ID, 'brand');
		if (!is_wp_error($terms) && !empty($terms) && is_object($terms[0])) {
			$taxonomy_slug = $terms[0]->slug;
		} else {
			// set a default if one isn't set.
			$taxonomy_slug = 'uk';
		}
		return str_replace('%brand%', $taxonomy_slug, $permalink);
	}

	// Register Custom Taxonomy
	public function register_custom_taxonomy()
	{
		$labels = array(
			'name'                       => _x( 'Brand', 'Taxonomy General Name', 'text_domain' ),
			'singular_name'              => _x( 'Brand', 'Taxonomy Singular Name', 'text_domain' ),
			'menu_name'                  => __( 'Brand', 'text_domain' ),
			'all_items'                  => __( 'All brands', 'text_domain' ),
			'parent_item'                => __( 'Parent brand', 'text_domain' ),
			'parent_item_colon'          => __( 'Parent brand:', 'text_domain' ),
			'new_item_name'              => __( 'New brand', 'text_domain' ),
			'add_new_item'               => __( 'Add brand', 'text_domain' ),
			'edit_item'                  => __( 'Edit brand', 'text_domain' ),
			'update_item'                => __( 'Update brand', 'text_domain' ),
			'view_item'                  => __( 'View brand', 'text_domain' ),
			'separate_items_with_commas' => __( 'Separate brands with commas', 'text_domain' ),
			'add_or_remove_items'        => __( 'Add or remove brands', 'text_domain' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
			'popular_items'              => __( 'Popular brands', 'text_domain' ),
			'search_items'               => __( 'Search brands', 'text_domain' ),
			'not_found'                  => __( 'Not Found', 'text_domain' ),
			'no_terms'                   => __( 'No brands', 'text_domain' ),
			'items_list'                 => __( 'Brands list', 'text_domain' ),
			'items_list_navigation'      => __( 'Brands list navigation', 'text_domain' ),
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
			'rest_base'          => 'brands',
			'rest_controller_class' => 'WP_REST_Terms_Controller',
		);
		register_taxonomy('brand', array( 'product' ), $args);
	}
	public function create_brand_reference() {
		$brand_object = json_encode($this->get_brand());
		echo "<script>window.agreableBrand = " . $brand_object . "</script>";
	}
}
new BrandTaxonomy();
