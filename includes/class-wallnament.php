<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.wallnament.com
 * @since      1.0.0
 *
 * @package    Wallnament
 * @subpackage Wallnament/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wallnament
 * @subpackage Wallnament/includes
 * @author     Wallnament <contact@wallnament.com>
 */
class Wallnament {

	const HOOKS = ['woocommerce_before_single_product', 'woocommerce_before_single_product_summary', 'woocommerce_single_product_summary',
		'woocommerce_before_add_to_cart_form', 'woocommerce_after_add_to_cart_form',
		'woocommerce_product_meta_start', 'woocommerce_product_meta_end', 'woocommerce_share', 'woocommerce_after_single_product'];

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wallnament_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WALLNAMENT_VERSION' ) ) {
			$this->version = WALLNAMENT_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wallnament';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wallnament_Loader. Orchestrates the hooks of the plugin.
	 * - Wallnament_i18n. Defines internationalization functionality.
	 * - Wallnament_Admin. Defines all hooks for the admin area.
	 * - Wallnament_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/checkbox-walker.php';
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wallnament-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wallnament-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wallnament-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wallnament-options-page.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wallnament-public.php';

		$this->loader = new Wallnament_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wallnament_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wallnament_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Wallnament_Admin( $this->get_plugin_name(), $this->get_version() );

		$options_page = new Wallnament_OptionsPage();

		$this->loader->add_filter('plugin_action_links_wallnament/wallnament.php', $options_page, 'plugin_settings_link');
		$this->loader->add_action( 'admin_menu', $options_page, 'add_menu_page' );
		$this->loader->add_action( 'admin_init', $options_page, 'settings_init' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Wallnament_Public( $this->get_plugin_name(), $this->get_version() );

		$options = get_option('wallnament_settings');
		$wc_targets = $options['public_wc_render_target'] ?? [];

		if($wc_targets) {
			foreach($wc_targets as $wc_target) {
				$this->loader->add_action($wc_target, $plugin_public, 'render_wc_widget');
			}
		}

		foreach(Wallnament::HOOKS as $hook_name) {
			$this->loader->add_action( $hook_name, null, function() use ($hook_name, $plugin_public) {
				$plugin_public->render_hook_preview($hook_name);
			});
		}

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action('wp_head', $plugin_public, 'render_header');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wallnament_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
