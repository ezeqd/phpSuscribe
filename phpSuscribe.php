<?php
/*
Plugin Name: phpSuscribe
Plugin URI: https://github.com/ezeqd/phpSuscribe
Description: Add a simple straightforward sign-up form to your WordPress site. Integrates with phpList, the most popular open-source newsletter manager.
Version: 1.0
Author: Ezequiel Domenech
Author URI: https://ezeqd.github.io/
Text Domain: php-suscribe
Network: true
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * smu_widget
 *
 * Register the widget
 *
 * @return void
 */
function smu_widget() {
	register_widget('smu_widget');
}
add_action('widgets_init', 'smu_widget');

/* 
 * Add any links to .css or .js files here
 */
function smu_add_css_js_files(){
	wp_enqueue_script('jQuery-Validation', plugins_url('/assets/js/lib/jquery.validation/1.13.1/jquery.validate.js', __FILE__), array('jquery'));
	wp_enqueue_style( 'smu-widget-stylesheet', plugins_url('/assets/css/style.css', __FILE__), false, '1.0.0', 'all');
}
add_action('wp_enqueue_scripts', "smu_add_css_js_files");



class smu_widget extends WP_Widget {

	/**
	 * smu_widget
	 *
	 * Setup the widget, register scripts etc
	 *
	 * @return void
	 */
	function __construct() {
		$widget_ops = array( 'classname' => 'smu-widget', 'description' => __('A simple sign-up form that integrates with phpList', 'smu') );
		parent::__construct( 'smu-widget', __('Sign Me Up', 'smu'), $widget_ops );

		add_action( 'init', array( $this, 'smu_register_script' ) ) ;

		// only load scripts when an instance is active
		if ( is_active_widget( false, false, $this->id_base ) && !is_admin())
			add_action( 'wp_footer', array( $this, 'smu_print_script' ) );

		add_action('wp_ajax_smu', array( $this, 'smu_ajax') );
		add_action('wp_ajax_nopriv_smu', array( $this, 'smu_ajax') );

	}

	/**
	 * widget
	 *
	 * Output the widget
	 *
	 * @return void
	 */
	function widget($args, $instance) {

		extract($args, EXTR_SKIP);
		$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
		$descr = empty($instance['descr']) ? '' : esc_attr( $instance['descr'] );
		$submit_url = esc_url( $instance['submit_url'] );
		$placeholder = empty($instance['placeholder']) ? '' : esc_attr( $instance['placeholder'] );
		$label = empty($instance['label']) ? '' : esc_attr( $instance['label'] );
		$button_txt = empty($instance['button_txt']) ? '' : esc_attr( $instance['button_txt'] );
		
		echo $before_widget;
		if (!empty($title)) {
			echo '<h2>' . $before_title . $title . $after_title . '</h2>' . PHP_EOL; 
			echo '<p>' . $descr . '</p>' . PHP_EOL;
		};
		
		//$wait_image_url = 'https://s3.amazonaws.com/phplist/img/busy.gif';
		$wait_image_url = plugins_url('/assets/images/busy.gif', __FILE__);
		
		?>
		
		<form class="smu-subscribe-form" method="post" action="<?php echo $submit_url; ?>">
			<label for="email"><?php echo $label; ?></label>
			<input type="email" id="smu_email" name="email" placeholder="<?php echo $placeholder; ?>" onfocus="smu_clearError()" required>
			<input type="submit" class="submit" value="<?php echo $button_txt; ?>">
			<img src="<?php echo $wait_image_url; ?>" class="wait-img" alt="Please wait" title="powered by phpList, www.phplist.com">
		</form>
		<div id="smu-jquery-error"></div>
		<div class="smu-server-response"></div>
		
		<?php
		echo $after_widget;

	}


	/**
	 * form
	 *
	 * Edit widget form
	 *
	 * @return void
	 */
	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => __('Sign Me Up', 'smu'), 'descr' => __('', 'smu'), 'submit_url' => __('', 'smu'), 'placeholder' => __('', 'smu'), 'label' => __('', 'smu'), 'button_txt' => __('', 'smu') ) );
		$title = esc_attr( $instance['title'] );
		$descr = esc_attr( $instance['descr'] );
		$submit_url = esc_url('submit_url');
		$placeholder = esc_attr( $instance['placeholder'] );
		$label = esc_attr( $instance['label'] );
		$button_txt = esc_attr( $instance['button_txt'] );

		?>
		
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'smu'); ?>:</label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('descr'); ?>"><?php _e('Text before form', 'smu'); ?>:</label>
			<textarea class="widefat" id="<?php echo $this->get_field_id('descr'); ?>" name="<?php echo $this->get_field_name('descr'); ?>"><?php echo $instance['descr']; ?></textarea>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('submit_url'); ?>"><?php _e('URL of phpList \'Subscribe Page\'', 'smu'); ?>:</label>
			<input class="widefat" id="<?php echo $this->get_field_id('submit_url'); ?>" name="<?php echo $this->get_field_name('submit_url'); ?>" type="text" value="<?php echo $instance['submit_url']; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('placeholder'); ?>"><?php _e('Placeholder text (inside input box)', 'smu'); ?>:</label>
			<input class="widefat" id="<?php echo $this->get_field_id('placeholder'); ?>" name="<?php echo $this->get_field_name('placeholder'); ?>" type="text" value="<?php echo $instance['placeholder']; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('label'); ?>"><?php _e('Label text (above input box)', 'smu'); ?>:</label>
			<input class="widefat" id="<?php echo $this->get_field_id('label'); ?>" name="<?php echo $this->get_field_name('label'); ?>" type="text" value="<?php echo $instance['label']; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('button_txt'); ?>"><?php _e('Button text (e.g, "Subscribe")', 'smu'); ?>:</label>
			<input class="widefat" id="<?php echo $this->get_field_id('button_txt'); ?>" name="<?php echo $this->get_field_name('button_txt'); ?>" type="text" value="<?php echo $instance['button_txt']; ?>" />
		</p>
		<?php
	}
	
	
	/**
	 * update
	 *
	 * Save the new widget instance
	 *
	 * @return array
	 */
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['descr'] = strip_tags($new_instance['descr']);
		$instance['submit_url'] = esc_url($new_instance['submit_url']);
		$instance['placeholder'] = strip_tags($new_instance['placeholder']);
		$instance['label'] = strip_tags($new_instance['label']);
		$instance['button_txt'] = strip_tags($new_instance['button_txt']);
		return $instance;
	}


	/**
	 * smu_register_script
	 *
	 * Register the JS for ajax request
	 *
	 * @return void
	 */
	function smu_register_script() {
		wp_register_script( 'smu', plugins_url('/assets/js/magic.js', __FILE__), array('jquery'), '1.0', true);

		wp_localize_script('smu','smu', array(
			'ajax_url' => esc_url(add_query_arg(array('action' => 'smu','_wpnonce' => wp_create_nonce( 'smu' )), untrailingslashit(admin_url('admin-ajax.php')))),
		));
	}


	/**
	 * smu_print_script
	 *
	 * Output JS only when widget in use on page
	 *
	 * @return void
	 */
	function smu_print_script() {
		wp_print_scripts( 'smu' );
	}


	
}

?>