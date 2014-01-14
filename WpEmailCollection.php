<?php
/*
Plugin Name: Email Collection
Plugin URI: 
Description: Collect email info.
Author: Austin Matzko
Author URI: https://austinmatzko.com
Version: 1.0
Text Domain: wp-email-collection
*/

if (!class_exists('WpEmailCollection')) {
	class WpEmailCollection
	{
		public function __construct()
		{
			add_action('widgets_init', array($this, 'register_widget'));
			add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		}

		/**
		 * Callback for enqueuing scripts.
		 */
		public function enqueue_scripts()
		{
			wp_enqueue_style(
				'wp-email-collection',
				plugin_dir_url( __FILE__ ) . 'client-files/css/style.css'
			);
		}

		/**
		 * Callback for registering the widget.
		 */
		public function register_widget()
		{
			if (class_exists('WpWidgetFormSubmit_Widget')) {
				register_widget('WpEmailCollection_EmailCollection');
			}
		}
	}

	/**
	 * Initialize the plugin into a global.
	 */
	function initialize_wp_email_collection_plugin()
	{
		global $wp_email_collection_plugin;
		$wp_email_collection_plugin = new WpEmailCollection();
	}

	/**
	 * Autoload classes used in this plugin.
	 *
	 * @param string $class The unknown class that PHP is looking for.
	 */
	function wp_email_collection_autoloader($class = '')
	{
		if (preg_match('/^(WpEmailCollection)_(.*)/i',$class, $matches) && $matches[2]) {
			$subdirs = explode('_', $matches[2]);
			$class_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . $matches[1];
			foreach($subdirs as $sub) {
				$class_file .= DIRECTORY_SEPARATOR . $sub;
			}
			$class_file .= '.php';
			if (file_exists($class_file)) {
				include_once $class_file;
			}
		}
	}

	/**
	 * Attach the callback for initializing this plugin.
	 */
	add_action('plugins_loaded', 'initialize_wp_email_collection_plugin');
	spl_autoload_register('wp_email_collection_autoloader');
}
