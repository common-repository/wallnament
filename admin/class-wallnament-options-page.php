<?php

class Wallnament_OptionsPage
{
	const ICON = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA4MTkyIDgxOTIiPgogIDxnIHRyYW5zZm9ybT0idHJhbnNsYXRlKDE5MikiPgogICAgPHBhdGggZD0iTTEzOTEuMSA2NjgzLjcgNDAwMy42IDgxOTJsMjYxMi41LTE1MDguMy0yNjEyLjUtMTUwOC4zeiIgZmlsbD0iI2E3YWFhZCIvPgogICAgPHBhdGggZD0iTTQ5MTAuNiA1MjMuNnYzODI3LjdsLTY2MS45LTM4MS40LTI0NS0xNDIuMi0yNDQuOSAxNDIuMi02NjIgMzgxLjRWNTIzLjZMNDAwMy43IDB6IiBmaWxsPSIjYTdhYWFkIi8+CiAgICA8cGF0aCBkPSJNNzU1Ni42IDIwNTEuM3YzODMzLjRsLTE2NTYuOC05NTUuNlYxMDkzLjl6IiBmaWxsPSIjYTdhYWFkIi8+CiAgICA8cGF0aCBkPSJNMjEwNy42IDEwOTMuOXYzODM1LjJMNDUwLjkgNTg4NC43VjIwNTEuM3oiIGZpbGw9IiNhN2FhYWQiLz4KICA8L2c+Cjwvc3ZnPgo=';

	public function add_menu_page()
	{
		add_menu_page('Wallnament', 'Wallnament', 'manage_options', 'wallnament', [$this, 'wallnament_options_page'], self::ICON);
	}

	public function wallnament_options_page()
	{
		if (!class_exists('woocommerce')) {
			?>
			<h1>We're sorry. This plugin requires WooCommerce to work properly.</h1>
			<p>Not something that you expected? Please <a href='mailto:contact@wallnament.com'>contact us</a>.</p>
			<?php
			return;
		}
		?>
		<form action='options.php' method='post' id='wallnament-settings-form'>
			<h2>Wallnament</h2>
			<?php
			settings_fields('pluginPage');
			do_settings_sections('pluginPage');
			submit_button();
			?>
		</form>
		<?php
	}


	function settings_init()
	{
		register_setting('pluginPage', 'wallnament_settings');
		if (!class_exists('woocommerce')) return;

		add_settings_section(
			'wallnament_pluginPage_section',
			__('WooCommerce Settings', 'wallnament'),
			function () {
			},
			'pluginPage'
		);


		add_settings_field(
			'secret_key',
			'Organization key',
			[$this, 'secret_key_renderer'],
			'pluginPage',
			'wallnament_pluginPage_section',
			['label_for' => 'secret_key_field']
		);

		add_settings_field(
			'mode',
			'Integration mode',
			[$this, 'mode_renderer'],
			'pluginPage',
			'wallnament_pluginPage_section',
			['label_for' => 'mode_field']
		);

		add_settings_field(
			'public_wc_render_target',
			__('Widget placement', 'wallnament'),
			[$this, 'public_wc_render_target_render'],
			'pluginPage',
			'wallnament_pluginPage_section',
			['label_for' => 'render_target_field']
		);

		add_settings_field(
			'allowed_categories',
			'Allowed categories',
			[$this, 'allowed_categories_render'],
			'pluginPage',
			'wallnament_pluginPage_section'
		);

		add_settings_field(
			'allowed_tags',
			'Allowed tags (empty if all)',
			[$this, 'allowed_tags_render'],
			'pluginPage',
			'wallnament_pluginPage_section',
			['label_for' => 'allowed_tags_field']
		);

		add_settings_field(
			'image_position',
			'Visualisation image selection',
			[$this, 'image_position_render'],
			'pluginPage',
			'wallnament_pluginPage_section',
			['label_for' => 'image_position_field']
		);

		add_settings_field(
			'dimensions_source',
			'Product dimensions source',
			[$this, 'dimensions_source_renderer'],
			'pluginPage',
			'wallnament_pluginPage_section',
			['label_for' => 'dimensions_source_field']
		);

		add_settings_field(
			'product_type',
			'Product type',
			[$this, 'product_type_renderer'],
			'pluginPage',
			'wallnament_pluginPage_section',
			['label_for' => 'product_type_field']
		);

		add_settings_field(
			'button_look',
			'Button look',
			[$this, 'button_look_renderer'],
			'pluginPage',
			'wallnament_pluginPage_section'
		);
	}

	function secret_key_renderer()
	{
		$value = get_option('wallnament_settings')['secret_key'] ?? '';
		?>
		<input type='text' id='secret_key_field' name='wallnament_settings[secret_key]'
			   value='<?php echo esc_attr($value ?? ''); ?>'>

		</td>
		<td>
		Your private organization key. You can find it in the client console at <a href="https://app.wallnament.com/app/settings" target="_blank">https://app.wallnament.com/app/settings</a>.
		<?php
	}

	function option_helper($option_value, $label, $value) {
		?>
		<option
			value='<?php echo esc_attr($option_value) ?>' <?php selected($value, $option_value); ?>>
			<?php echo esc_html($label) ?>
		</option>
		<?php
	}


	function public_wc_render_target_render()
	{
		$value = get_option('wallnament_settings')['public_wc_render_target'] ?? [];

		$params = array(
			'posts_per_page' => 1,
			'post_type' => 'product'
		);

		$wc_query = new WP_Query($params);

		global $post;
		$product = null;

		if($wc_query->have_posts()) {
			$wc_query->the_post();

			$product = wc_get_product($post);
		}

		?>
		<select multiple="multiple" id='render_target_field' name='wallnament_settings[public_wc_render_target][]'>
			<?php
			foreach(Wallnament::HOOKS as $hook_name) {
				$selected = in_array($hook_name, $value) ? "selected" : "";
				echo "<option value='" . esc_attr($hook_name) . "' " . $selected . " >" . esc_html($hook_name) . "</option>";
			}
			?>
		</select>
		</td>
	<td>
			Place when widget is embedded. Please use
			<a href="https://storecustomizer.com/woocommerce-product-page-hooks-visual-guide/" target="_blank" ref="noreferrer noopener">WooCommerce Product Page Hooks guide</a>


		<?php
		if($product) {
			$link = $product->get_permalink();
			if (strpos($link, '?') == false) {
				$link .= "?";
			} else {
				$link .= "&";
			}
			$link .= "wallnament-hook-preview=1";

			echo "or <a href='" . esc_url($link) . "' target='_blank'>check it out on your page</a>";
		}

		wp_reset_query();
		?>
		to find a spot that works best for your store. You can use multiple placements at once.
		<?php
	}

	function allowed_categories_render()
	{
		?>
		<ul class='checkbox-walker'>
		<?php wp_list_categories(['taxonomy' => 'product_cat', 'hide_empty' => 0, 'walker' => new CheckboxWalker('wallnament_settings', 'allowed_categories'), 'title_li' => false]); ?>
		</ul>
		</td>
		<td>
			Select product categories for which Wallnament widget should appear
		<?php
	}

	function allowed_tags_render() {
		$value = get_option('wallnament_settings')['allowed_tags'] ?? '';

		?>
		<input type="text" id='allowed_tags_field' name='wallnament_settings[allowed_tags]' value="<?php echo esc_attr($value); ?>" />
		</td>
		<td>
			<p>If you want to show Wallnament only on products with specific tags then fill this field.
				Separate allowed tags with commas.</p>
		<p>If you don't need filtering then leave this empty.</p>

		<?php
	}

	function image_position_render() {
		$value = get_option('wallnament_settings')['image_position'] ?? '';

		?>
		<select id='image_position_field' name='wallnament_settings[image_position]'>
			<option disabled value='' <?php selected($value, ''); ?>>Select…</option>
			<option
				value='0' <?php selected($value, '0'); ?>>
				Main image
			</option>
			<option
				value='1' <?php selected($value, '1'); ?>>
				First image from gallery
			</option>
			<option
				value='2' <?php selected($value, '2'); ?>>
				Second image from gallery
			</option>
			<option
				value='3' <?php selected($value, '3'); ?>>
				Third image from gallery
			</option>
			<option
				value='4' <?php selected($value, '4'); ?>>
				Fourth image from gallery
			</option>
		</select>
		</td>
		<td>
			Wallnament needs a raw picture showing your item without frames or additional background. Select which product's image fits that criteria.
		<?php
	}

	function dimensions_source_renderer() {
		$value = get_option('wallnament_settings')['dimensions_source'] ?? '';
		$details_value = get_option('wallnament_settings')['dimensions_source_details'] ?? '';

		?>
		<select id='dimensions_source_field' name='wallnament_settings[dimensions_source]'>
			<option disabled value='' <?php selected($value, ''); ?>>Select…</option>
			<option
				value='shipping' <?php selected($value, 'shipping'); ?>>
				Shipping settings
			</option>
			<option
				value='free' <?php selected($value, 'free'); ?>>
				Free size
			</option>
			<option
				value='static' <?php selected($value, 'static'); ?>>
				Static value
			</option>
			<option
				value='attribute' <?php selected($value, 'attribute'); ?>>
				Attribute
			</option>
		</select>
		<input id='dimensions_source_details_field' type="text" class="hidden" name="wallnament_settings[dimensions_source_details]" value="<?php echo esc_attr($details_value) ?>" />
		<script>
			const selector = document.getElementById("dimensions_source_field");
			const customField = document.getElementById("dimensions_source_details_field");

			const updateField = () => {
				customField.classList.toggle("hidden", selector.value === "shipping" || selector.value === "free");
				customField.placeholder = ({
					static: "Static value, e.g. '15x20cm'",
					attribute: "Attribute name"
				})[selector.value] || '';
			}
			selector.onchange = () => {
				updateField();
				customField.value = '';
			};
			updateField();
		</script>
		</td>
		<td>
			Wallnament needs to know the size of your item. You have few options:
			<ul>
				<li>use product dimensions defined in the <b>Shipping Settings</b></li>
				<li>With <b>free size</b> your clients will be able to adjust the size in the application</li>
				<li>use one <b>Static value</b> for all your products</li>
				<li>fetch the dimension from a Product <b>Attribute</b>. You need to provide the attribute name.</li>
			</ul>
		<?php
	}

	function mode_renderer() {
		$value = get_option('wallnament_settings')['mode'] ?? '';
		?>
		<select id='mode_field' name='wallnament_settings[mode]'>
			<option disabled value='' <?php selected($value, ''); ?>>Select…</option>
			<?php
				$this->option_helper("disabled", "Disabled", $value);
				$this->option_helper("admin_only", "Visible only for admin", $value);
				$this->option_helper("enabled", "Enabled", $value);
			?>
		</select>

		</td>
		<td>
			<ul>
				<li>Disabled - widget is not visible</li>
				<li>Visible only for admin - widget is visible only when you are signed in as an admin</li>
				<li>Enabled - widget is visible to every visitor</li>
			</ul>
		<?php

	}

	function product_type_renderer() {
		$value = get_option('wallnament_settings')['product_type'] ?? '';
		?>
		<select id="product_type_field" name="wallnament_settings[product_type]">
			<?php
				$this->option_helper("generic", "Generic", $value);
				$this->option_helper("poster", "Poster", $value);
				$this->option_helper("painting", "Painting", $value);
				$this->option_helper("sticker", "Sticker", $value);
			?>
		</select>
		</td><td>
			Product type affects product rendering. Please select correct type depending on your store offer.
			<p>Selling multiple product types in one store? Please <a href="mailto:contact@wallnament.com">contact us</a>.</p>
		<?php
	}

	function button_look_renderer() {
		$value = get_option('wallnament_settings')['button_look'] ?? '';

		$language = explode("_", get_locale() ?? "")[0] ?? "en";
		?>

		<?php add_thickbox(); ?>
		<input id="button-look-value" type="hidden" name="wallnament_settings[button_look]" value='<?php echo esc_attr($value ?? ''); ?>' />
		<a id="wallnament-customize-button-look" class="thickbox button button-primary" type="button">Customize</a>
</td><td>
		<div style="margin-bottom: 10px">Current preview (for "<?php echo esc_html($language) ?>" language):</div>
		<div id="wallnament-button-preview"></div>
		<p>Button language is determined based on you website's <a href="https://www.w3schools.com/tags/ref_language_codes.asp">HTML lang</a>. When not set English is default.</p>
		<script>
			function __WallnamentInitPreview() {
				let configuration;

				try {
					const storedValue = document.getElementById("button-look-value").value;
					if(storedValue.length) {
						configuration = JSON.parse(storedValue);
					}
				} catch {}

				window.InstallWallnamentEditableButtonPreview({ elementId: "wallnament-button-preview", languageSwitcher: false, defaultLanguage: "<?php echo esc_js($language) ?>", initialConfiguration: configuration  });
			}

			(function() {
				const baseUrl = "https://app.wallnament.com/widget/button-customization-frame?language=<?php echo esc_url($language) ?>&";
				const tbPart = "TB_iframe=true&width=700&height=580";

				const customizeButton = document.getElementById("wallnament-customize-button-look");
				const valueInput = document.getElementById("button-look-value");

				function encodeUrl() {
					const value = valueInput.value;

					if (value && value.length) {
						customizeButton.href = baseUrl + "config=" + encodeURIComponent(value) + "&" + tbPart;
					} else {
						customizeButton.href = baseUrl + tbPart;
					}
				}

				encodeUrl();

				const listener = function (e) {
					if (e.data && e.data.source === "wallnament-button-customizer-update") {
						if(e.data.action === "apply") {
							valueInput.value = JSON.stringify(e.data.data);
							encodeUrl();
						}
						tb_remove();
					}
				};
				window.addEventListener("message", listener);
			})();
		</script>
		<script onload="__WallnamentInitPreview()" async src="https://app.wallnament.com/packs/button_customizer_preview.js"></script>
		<?php
	}

	function plugin_settings_link( array $links ) {
		$url = get_admin_url() . "admin.php?page=wallnament";
		$settings_link = '<a href="' . $url . '">' . __('Settings', 'textdomain') . '</a>';
		$links[] = $settings_link;
		return $links;
	}
}
