<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.wallnament.com
 * @since      1.0.0
 *
 * @package    Wallnament
 * @subpackage Wallnament/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wallnament
 * @subpackage Wallnament/public
 * @author     Wallnament <contact@wallnament.com>
 */
class Wallnament_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */

	private $counter;

	public function __construct( $plugin_name, $version ) {
		$this->counter = 0;
		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public function render_header() {
		if (function_exists('is_product') && is_product()) {
			include "partials/widget-header-script.php";
		}
	}

	private function split_tags($input) {
		return array_filter(
			array_map(
				function($input) { return trim($input); },
				explode(",", $input)
			),
			function($input) { return !!$input; }
		);
	}

	/*
	 * Mostly copied from $product->get_attribute()
	 */
	private function product_raw_attribute($product, $dimensions_source_details) {
		$attributes = $product->get_attributes();
		$attribute  = sanitize_title( $dimensions_source_details );

		if ( isset( $attributes[ $attribute ] ) ) {
			$attribute_object = $attributes[ $attribute ];
		} elseif ( isset( $attributes[ 'pa_' . $attribute ] ) ) {
			$attribute_object = $attributes[ 'pa_' . $attribute ];
		} else {
			$attribute_object = false;
		}

		if($attribute_object) {
			return $attribute_object->is_taxonomy() ? wc_get_product_terms($product->get_id(), $attribute_object->get_name(), array('fields' => 'names')) : $attribute_object->get_options();
		}
	}

	public function render_wc_widget() {
		global $product;

		if(!$product) return;

		$options = get_option('wallnament_settings');

		$mode = $options['mode'] ?? false;

		if ($mode === 'admin_only') {
			if (!current_user_can('administrator')) {
				return;
			}
		} else if ($mode !== 'enabled') {
			return;
		}

		$allowed_categories = $options['allowed_categories'] ?? [];
		if (empty(array_intersect($product->get_category_ids(), array_keys($allowed_categories)))) {
			return;
		}
		$allowed_tags = $this->split_tags($options['allowed_tags'] ?? '');
		if($allowed_tags) {
			$allowed = false;
			$tag_ids = $product->get_tag_ids();
			foreach($tag_ids as $tag_id) {
				if(in_array(get_term($tag_id)->name, $allowed_tags)) {
					$allowed = true;
					break;
				}
			}
			if(!$allowed) return;
		}

		$secret_key = $options['secret_key'] ?? false;
		$image_position = $options['image_position'] ?? false;
		$dimension_source = $options['dimensions_source'] ?? false;
		$dimensions_source_details = $options['dimensions_source_details'] ?? false;
		$product_type = $options['product_type'] ?? 'generic';
		$raw_button_look = $options['button_look'] ?? false;

		$width = 139;
		$height = 44;
		$button_look = null;
		if($raw_button_look) {
			$button_look = json_decode($raw_button_look);
			$width = $button_look->width ?? $width;
			$height = $button_look->height ?? $height;
		}

		$dimensions = false;
		if ($dimension_source === 'shipping') {
			$unit = get_option( 'woocommerce_dimension_unit' );
			$dimensions = $product->get_width() . $unit . " x " . $product->get_length() . $unit;
		} else if ($dimension_source === 'static') {
			$dimensions = $dimensions_source_details;
		} else if ($dimension_source === 'attribute' && $dimensions_source_details) {
			$dimensions = $this->product_raw_attribute($product, $dimensions_source_details);
		}

		$image_url = false;
		if(is_numeric($image_position)) {
			$image_id = $image_position === '0' ?
				$product->get_image_id() :
				$product->get_gallery_image_ids()[intval($image_position) - 1] ?? false;

			if($image_id) {
				$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			}
		}

		if(!$secret_key || !$image_url) {
			 return;
		}

		?>
		<script>
	Wallnament({
		selector: "#ar-button-<?php echo esc_js($this->counter) ?>",
		key: "<?php echo esc_js($secret_key) ?>",
		platform: "wp",
		link: "<?php echo esc_js($product->get_permalink()) ?>",
		name: "<?php echo esc_js($product->get_name()) ?>",
		product_type: "<?php echo esc_js($product_type) ?>",
		<?php if($dimensions) echo 'dimensions: ' . wp_json_encode($dimensions) . ",\n" ?>
		<?php if($button_look) echo 'buttonConfig: ' . wp_json_encode($button_look) . ",\n" ?>
		image: "<?php echo esc_js($image_url) ?>"
	});
</script>
<div id="ar-button-<?php echo esc_attr($this->counter) ?>" style="display: inline-block; width: <?php echo $width ?>px; height: <?php echo $height ?>px; margin-top: 1rem; margin-bottom: 1rem;"></div>
<?php

		$this->counter++;
	}

	public function render_hook_preview($hook_name) {
		$enabled = sanitize_text_field($_GET["wallnament-hook-preview"] ?? false);
		if(current_user_can('administrator') && $enabled) {
			echo '<div class="wallnament-hook-preview">' . esc_html($hook_name) . '</div>';
		}
	}

		/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wallnament_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wallnament_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wallnament-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wallnament_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wallnament_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wallnament-public.js', array( 'jquery' ), $this->version, false );

	}

}
