<?php

class CheckboxWalker extends Walker_Category
{
	private $option_name;
	private $selector;

	public function __construct($option_name, $selector) {
		$this->option_name = $option_name;
		$this->selector = $selector;
	}

	public function start_el(&$output, $category, $depth = 0, $args = array(), $id = 0)
	{
		extract($args);

		$cat_name = esc_attr($category->name);
		$cat_name = apply_filters('list_cats', $cat_name, $category);

		$output .= "\t<li";
		$class = 'cat-item cat-item-' . $category->term_id;

		if (!empty($current_category)) {
			$_current_category = get_term($current_category, $category->taxonomy);
			if ($category->term_id == $current_category)
				$class .= ' current-cat';
			elseif ($category->term_id == $_current_category->parent)
				$class .= ' current-cat-parent';
		}
		$output .= ' class="' . $class . '"';
		$output .= "><label><input type=\"checkbox\" name=\"$this->option_name[$this->selector][$category->term_id]\" value='1' " .
			checked(1, get_option($this->option_name)[$this->selector][$category->term_id] ?? false, false) .
			" />&nbsp;$cat_name</label>";
	}

}

?>
