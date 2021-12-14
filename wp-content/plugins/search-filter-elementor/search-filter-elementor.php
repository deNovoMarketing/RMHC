<?php
/**
 * Plugin Name: Search & Filter - Elementor Extension
 * Description: Adds Search & Filter integration for Elementor - filter your Posts, Posts Archive, Portfolio, Products & Products Archive widgets
 * Plugin URI:  https://searchandfilter.com
 * Version:     1.0.8
 * Author:      Code Amp
 * Author URI:  https://codeamp.com
 * Text Domain: search-filter-elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main Elementor Extension Class
 *
 * The main class that initiates and runs the plugin.
 *
 * @since 1.0.0
 */
final class Search_Filter_Elementor_Extension {

	/**
	 * Plugin Version
	 *
	 * @since 1.0.0
	 *
	 * @var string The plugin version.
	 */
	const VERSION = '1.0.8';

	/**
	 * Minimum Elementor Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum Elementor version required to run the plugin.
	 */
	const MINIMUM_ELEMENTOR_VERSION = '2.0.0';

	/**
	 * Minimum PHP Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum PHP version required to run the plugin.
	 */
	const MINIMUM_PHP_VERSION = '7.0';

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @static
	 *
	 * @var Search_Filter_Elementor_Extension The single instance of the class.
	 */
	private static $_instance = null;
	
	private $search_form_options = array();
	public $current_products_query_id = 0;
	public $wc_no_results_message = '';
	public $display_methods = array();
	
	const PLUGIN_UPDATE_URL = 'https://searchandfilter.com';
	const PLUGIN_UPDATE_ID = 278073; 

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @return Search_Filter_Elementor_Extension An instance of the class.
	 */
	 
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;

	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function __construct() {

		add_action( 'init', [ $this, 'i18n' ] );
		add_action( 'plugins_loaded', [ $this, 'init' ] );

	}

	/**
	 * Load Textdomain
	 *
	 * Load plugin localization files.
	 *
	 * Fired by `init` action hook.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function i18n() {

		load_plugin_textdomain( 'search-filter-elementor' );

	}

	/**
	 * Initialize the plugin
	 *
	 * Load the plugin only after Elementor (and other plugins) are loaded.
	 * Checks for basic plugin requirements, if one check fail don't continue,
	 * if all check have passed load the files required to run the plugin.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function init() {

		// Check if Elementor installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
			return;
		}

		// Check for required Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
			return;
		}

		// Check for required PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
			return;
		}
		
		// Elementor Widgets
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'init_widgets' ] );
		
		// Scripts
		add_action('wp_enqueue_scripts', array($this, "enqueue_scripts"), 10);
		
		
		// Porfolio + Posts element 
		
		// Modify controls
		add_action( 'elementor/element/posts/section_query/before_section_end', array($this, 'modify_posts_controls'), 10, 2 ); //posts
		add_action( 'elementor/element/portfolio/section_query/before_section_end', array($this, 'modify_posts_controls'), 10, 2 ); //posts
		add_action( 'elementor/element/ae-post-blocks/section_query/before_section_end', array($this, 'modify_ae_post_blocks_controls'), 10, 2 ); //posts
		
		// Frontend
		add_filter( 'elementor/query/query_args', array($this, 'attach_sf_to_posts'), 10, 2 ); //attach S&F to the query
		add_action( 'elementor/widget/before_render_content', array( $this, 'filter_posts_before_render' ), 10 ); //add classes, attach to query
		add_action( 'elementor/widget/before_render_content', array( $this, 'filter_ae_before_render' ), -10 ); //add classes, attach to query
		add_filter( 'elementor/widget/render_content', array( $this, 'filter_posts_render' ), 10, 2 ); //filter the rendered content to fix some things (hacky)
		
		
		// Products element
		
		// Modify Controls
		add_action( 'elementor/element/woocommerce-products/section_query/before_section_end', array($this, 'modify_products_controls'), 10, 2 );
		
		// Frontend
		add_action( 'elementor/frontend/before_render', array( $this, 'before_render_products_element' ), 10 );
		add_action( 'elementor/frontend/after_render', array( $this, 'after_render_products_element' ), 10 );
		
		
		// Archive Elements (Products, Post Types)
		add_action( 'elementor/widget/before_render_content', array( $this, 'filter_archives_before_render' ), 10 ); //add classes, attach to query
		
		// Filter Anywhere Elementor query
		//add_filter('aepro/post-blocks/query_filter', array( $this, 'filter_ae_query' ), 10, 2 );
		
		// Search & Filter Additions
		add_filter( 'search_filter_admin_option_display_results', array($this, 'search_filter_admin_option_display_results'), 10, 2 ); //add a display option to the search form
		add_filter( 'search_filter_form_attributes', array($this, 'search_filter_form_attributes'), 10, 2 );
		
		
		// Plugin Updater
		add_action( 'admin_init', array($this, 'update_plugin_handler'), 0 );

		// register as a S&F extension:
		add_action( 'search_filter_extensions', array($this, 'add_extension'), 0 );

	}
	
	/**
	 * Handle plugin updates
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function update_plugin_handler() {
		
		// setup the updater
		$edd_updater = new Search_Filter_Elementor_Plugin_Updater( self::PLUGIN_UPDATE_URL, __FILE__,
			array(
				'version' => self::VERSION,
				'license' => 'search-filter-extension-free',
				'item_id' => self::PLUGIN_UPDATE_ID,       // ID of the product
				'author'  => 'Search & Filter', // author of this plugin
				'beta'    => false,
			)
		);
		
	}

	
	public function add_extension( $extensions ) {
		array_push( $extensions, 'search-filter-elementor' );
		return $extensions;
	}
	/**
	 *
	 * Load Scripts
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function enqueue_scripts(){	
	
		wp_register_script( 'search-filter-elementor', plugins_url( 'assets/js/search-filter-elementor.js', __FILE__ ), array( 'jquery' ), "1.0.0" );
		wp_localize_script( 'search-filter-elementor', 'SFE_DATA', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'home_url' => (home_url('/')) ));
		wp_enqueue_script( 'search-filter-elementor' );
	}
	
	private function get_search_form_options(){

		if(empty($this->search_form_options)){

			$posts_query = 'post_type=search-filter-widget&post_status=publish&posts_per_page=-1';
			
			if ( class_exists( 'Search_Filter_Helper' )) {
				if( Search_Filter_Helper::has_polylang() ){
					$posts_query .= '&lang=all';
				}
			}
			
			$custom_posts = new WP_Query( $posts_query );
			
			if( $custom_posts->post_count > 0 ){
				foreach ($custom_posts->posts as $post){
					$this->search_form_options[$post->ID] = html_entity_decode(esc_html($post->post_title) );
				}
			}
		}
		
		return $this->search_form_options;
	}
	
	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Elementor installed or activated.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_missing_main_plugin() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'search-filter-elementor' ),
			'<strong>' . esc_html__( 'Search & Filter - Elementor Extension', 'search-filter-elementor' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor Pro', 'search-filter-elementor' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required Elementor version.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_minimum_elementor_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'search-filter-elementor' ),
			'<strong>' . esc_html__( 'Elementor Test Extension', 'search-filter-elementor' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'search-filter-elementor' ) . '</strong>',
			 self::MINIMUM_ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required PHP version.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_minimum_php_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( 'The "%1$s" requires "%2$s" version %3$s or greater.', 'search-filter-elementor' ),
			'<strong>' . esc_html__( 'Search & Filter Elementor Extension', 'search-filter-elementor' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'search-filter-elementor' ) . '</strong>',
			 self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Init Search & Filter Elementor widgets
	 *
	 * Include widgets files and register them
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function init_widgets() {

		// Include Widget files
		require_once( __DIR__ . '/widgets/search-filter-form.php' );

		// Register widget
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Search_Filter_Form() );

	}

	/**
	 * Init Controls (not in use)
	 *
	 * Include controls files and register them
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function init_controls() {

		// Include Control files
		require_once( __DIR__ . '/controls/test-control.php' );

		// Register control
		\Elementor\Plugin::$instance->controls_manager->register_control( 'control-type-', new \Test_Control() );

	}
	
	/**
	 * Add S&F to Products widgets query args
	 * 
	 * Filter the query_args before a WC shortcode query is run, attaches S&F to the args
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function attach_sf_to_wc_shortcode_args( $query_args, $attributes, $type ) {
		
		$query_args = $this->attach_sf_to_args($query_args, $this->current_products_query_id);
		return $query_args;
	}
	
	/**
	 * Add S&F to supported widgets query args
	 * 
	 * Filter for query args, fired before a query is run in Elementor
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function attach_sf_to_posts( $query_args, $widget  ) {
		$widget_name = "posts";
		return $this->attach_sf_to_widget_args($query_args, $widget, $widget_name);
	}
	
	/**
	 * Check if S&F can be attached
	 * 
	 * Checks a widgets settings are actually set to use S&F, before attaching 
	 * S&F to the query_args
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function attach_sf_to_widget_args( $query_args, $widget, $widget_name  ) {
		
		$widget_settings = $widget->get_settings();
		//get widget name fro msettings ?  then use that as prefix instead of `posts`
		
		if( !isset( $widget_settings [ $widget_name.'_post_type' ] ) ) {
			return $query_args;
		}
		
		if( 'search_filter_query' !== $widget_settings[ $widget_name.'_post_type' ] ) {
			return $query_args;
		}
		
		if(!isset($widget_settings['search_filter_query'])){
			return $query_args;
		}
		
		$query_args = $this->attach_sf_to_args($query_args, $widget_settings['search_filter_query']);
		return $query_args;
	}
	
	/**
	 * Attach S&F to query args
	 * 
	 * Takes a $args that will be passed to a WP_Query, and attaches S&F to it
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	
	private function attach_sf_to_args( $query_args, $sfid  ) {
		
		$query_args['search_filter_id'] = intval($sfid);
		return $query_args;
	}
	
	/**
	 * Update control dependencies
	 * 
	 * Takes a control stack, and adds dependencies to known fields so that
	 * they are hidden when S&F is set as the query source for elements
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function update_control_dependencies( $control_stack, $args, $widget_name  ) {
		
		$control_names_to_be_mod = array(
			'query_args',
			'query_include',
			'query_exclude',
			'posts_ids',
			'include',
			'include_term_ids',
			'related_taxonomies',
			'include_authors',
			'exclude',
			'exclude_ids',
			'exclude_term_ids',
			'exclude_authors',
			'avoid_duplicates',
			'offset',
			'related_fallback',
			'fallback_ids',
			'select_date',
			'date_before',
			'date_after',
			'orderby',
			'order',
			'ignore_sticky_posts',
			'query_id',
		);
		
		foreach ($control_names_to_be_mod as $control_name){
				
			$widget_control_name = $widget_name . '_' . $control_name;
			$control = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $control_stack->get_unique_name(), $widget_control_name );
			
			//var_dump($control_stack);
			if ( !is_wp_error( $control ) ) {
				
				//make sure we preserve existing conditions when we create / add our own to the list of controls
				if(!isset($control['condition'])){
					$control['condition'] = array();
				}
				if(!is_array($control['condition'])){
					$control['condition'] = array();
				}
				if(!isset($control['condition'][$widget_name.'_post_type!'])){
					$control['condition'][$widget_name.'_post_type!'] = array();
				}
				if(!is_array($control['condition'][$widget_name.'_post_type!'])){
					$control['condition'][$widget_name.'_post_type!'] = array($control['condition'][$widget_name.'_post_type!']);
				}
				
				array_push($control['condition'][$widget_name.'_post_type!'], 'search_filter_query');
					
				// Update the control
				$control_stack->update_control( $widget_control_name, $control );
			}
		}
	}
	private function update_ae_control_dependencies( $control_stack, $args, $widget_name  ) {
		
		if ( 'ae' !== $widget_name ) {
			return;
		}

		$control_names = [
			'author_ae_ids',
			'tax_relation',
			'current_post',
			'advanced',
			'orderby',
			'order',
			'posts_per_page',
			'offset',
		 ];
 
		 foreach($control_names as $control_name){
 
			 
			 $control = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $control_stack->get_unique_name(), $control_name );
			 
			 //var_dump($control_stack);
			 if ( !is_wp_error( $control ) ) {
				 
				 //make sure we preserve existing conditions when we create / add our own to the list of controls
				 if(!isset($control['condition'])){
					 $control['condition'] = array();
				 }
				 if(!is_array($control['condition'])){
					 $control['condition'] = array();
				 }
				 if(!isset($control['condition']['ae_post_type!'])){
					 $control['condition']['ae_post_type!'] = array();
				 }
				 if(!is_array($control['condition']['ae_post_type!'])){
					 $control['condition']['ae_post_type!'] = array($control['condition']['ae_post_type!']);
				 }
				 
				 array_push($control['condition']['ae_post_type!'], 'search_filter_query');
					 
				 // Update the control
				 $control_stack->update_control( $control_name, $control );
			 }
 
		 }
	}
	
	
	/**
	 * Extend Elementor controls to include options for S&F
	 *
	 * Will take a control stack and adds an option + control for S&F - to be
	 * be used with widgets with a query section (posts, portfolio, products)
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	
	private function modify_query_widget_controls( $control_stack, $args, $widget_name  ) {
		
		//all these controls need to be hidden when S&F Query is selected, so add the condition
		$this->update_control_dependencies($control_stack, $args, $widget_name);
		
		//now add S&F query option to the query source control:
		$control = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $control_stack->get_unique_name(), $widget_name.'_post_type'  );
		
		if ( ! is_wp_error( $control ) ) {
			
			// add extra condition
			$control['options']['search_filter_query'] = 'Search & Filter Query';
			
			// Update the control
			$control_stack->update_control( $widget_name.'_post_type', $control );
		}

		//now add the dropdown to choose a S&F query
		$control_stack->add_control( 'search_filter_query',
			array(
				'label' => 'Search & Filter Query' ,
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $this->get_search_form_options(),
				'condition' => [
					$widget_name.'_post_type' => 'search_filter_query'
				]
			)
		);
		
		if ( $widget_name !== 'ae' ) {
			//now add the dropdown to choose a S&F query
			$control_stack->add_control( 'search_filter_no_results',
				array(
					'label' => 'Nothing Found Message' ,
					'type' => \Elementor\Controls_Manager::TEXTAREA,
					'default' => '',
					'value' => '',
					'condition' => [
						$widget_name.'_post_type' => 'search_filter_query'
					]
				)
			);
		}
	}
	
	private function modify_ae_widget_controls( $control_stack, $args, $widget_name  ) {
		
		//all these controls need to be hidden when S&F Query is selected, so add the condition
		$this->update_ae_control_dependencies($control_stack, $args, $widget_name);
		
		//now add S&F query option to the query source control:
		$control = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $control_stack->get_unique_name(), $widget_name.'_post_type'  );
		
		if ( ! is_wp_error( $control ) ) {
			
			// add extra condition
			$control['options']['search_filter_query'] = 'Search & Filter Query';
			
			// Update the control
			$control_stack->update_control( $widget_name.'_post_type', $control );
		}

		//now add the dropdown to choose a S&F query
		$control_stack->add_control( 'search_filter_query',
			array(
				'label' => 'Search & Filter Query' ,
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $this->get_search_form_options(),
				'condition' => [
					$widget_name.'_post_type' => 'search_filter_query'
				]
			)
		);
		
		if ( $widget_name !== 'ae' ) {
			//now add the dropdown to choose a S&F query
			$control_stack->add_control( 'search_filter_no_results',
				array(
					'label' => 'Nothing Found Message' ,
					'type' => \Elementor\Controls_Manager::TEXTAREA,
					'default' => '',
					'value' => '',
					'condition' => [
						$widget_name.'_post_type' => 'search_filter_query'
					]
				)
			);
		}
	}
	
	/**
	 * Extend widget controls for posts + portfolio widget
	 * 
	 * Takes a posts or portfolio widget control stack, and pass the correct prefix
	 * for setting up the widget controls
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function modify_posts_controls( $control_stack, $args  ) {
		
		//for posts + portfolio element, the prefix to access settings is `posts`
		$widget_name = 'posts';
		$this->modify_query_widget_controls( $control_stack, $args, $widget_name  );
			
	}
	/**
	 * Extend widget for Anywhere Elementor
	 * 
	 * Takes a control stack, and pass the correct prefix
	 * for setting up the widget controls
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function modify_ae_post_blocks_controls( $control_stack, $args  ) {
		
		//for posts + portfolio element, the prefix to access settings is `posts`
		$widget_name = 'ae';
		$this->modify_ae_widget_controls( $control_stack, $args, $widget_name  );
			
	}
	
	/**
	 * Extend widget controls for product widget
	 * 
	 * Takes a products widget control stack, and pass the correct prefix
	 * for setting up the widget controls
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function modify_products_controls( $control_stack, $args  ) {
		//for products element, the prefix to access settings is `query`
		$widget_name = 'query';
		$this->modify_query_widget_controls( $control_stack, $args, $widget_name  );
			
	}
	
	/**
	 * Add S&F results class to posts + porfolio widgets
	 * 
	 * Add a class to the widget on frontend, as well as jumping in and attaching hook 
	 * to modify `found_posts` (for the hack around the "no results message")
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function filter_posts_before_render($element){
		
		//$sf_widget_types = array('posts', 'portfolio', 'products');
		$sf_element_types = array('posts', 'portfolio');
		//echo $element->get_name();
		if(in_array($element->get_name(), $sf_element_types)){
			
			$element_data = $element->get_data();
			$element_settings = $element_data['settings'];
			
			if(!isset($element_settings['search_filter_query'])){
				return;
			}
			
			if( ( 'posts' === $element->get_name() ) || ( 'portfolio' === $element->get_name() ) ){
				
				if( !isset( $element_settings[ 'posts_post_type' ]) ) {
					return;
				}
				
				if( 'search_filter_query' !== $element_settings[ 'posts_post_type' ] ) {
					return;
				}
				
				// catch the query thats generated before its used - so we can adjust `found_posts` and make elementor think
				// it needs to be rendered
				add_action( 'elementor/query/query_results', array( $this, 'adjust_elementor_query_found_posts' ), 100 );
				
			}
			
			$sfid = intval($element_settings['search_filter_query']);
			
			$element_class = 'search-filter-results-'.$sfid;
			
			$args = array(
				'class' => array($element_class)
			);
			
			$element->add_render_attribute('_wrapper', $args);
		}
	}
	
	/**
	 * Add S&F results class to AE widgets
	 * 
	 * Add a class to the widget on frontend
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function filter_ae_before_render($element){
		
		$sf_element_types = array( 'ae-post-blocks' );
		//echo $element->get_name();
		if(in_array($element->get_name(), $sf_element_types)){
			
			$element_data = $element->get_data();
			$element_settings = $element_data['settings'];
			
			if(!isset($element_settings['search_filter_query'])){
				return;
			}
			
			//if( ( 'posts' === $element->get_name() ) || ( 'portfolio' === $element->get_name() ) ){
				
				if( !isset( $element_settings[ 'ae_post_type' ]) ) {
					return;
				}
				
				if( 'search_filter_query' !== $element_settings[ 'ae_post_type' ] ) {
					return;
				}
		//	}
			
			$sfid = intval($element_settings['search_filter_query']);
			
			$element_class = 'search-filter-results-'.$sfid;
			
			$args = array(
				'class' => array($element_class)
			);
			
			$element->add_render_attribute('_wrapper', $args);

			global $searchandfilter;
			if ( $searchandfilter ) {
				$searchform = $searchandfilter->get($sfid);
				$searchform->query()->prep_query();
			}
			// attach S&F to query
			add_filter( 'aepro/post-blocks/custom-source-query', array( $this, 'filter_ae_query'), -1, 2 );
			
		}
	}
	public function filter_ae_query( $query_args, $settings ) {
		
		if( ! isset($settings['search_filter_query'] ) ) {
			return $query_args;
		}
		if( ! isset($settings['ae_post_type'] ) ) {
			return $query_args;
		}
		if( 'search_filter_query' !== $settings['ae_post_type'] ) {
			return $query_args;
		}
		$sfid = intval( $settings['search_filter_query'] );
		//$query_args['post_type'] = 'post'; //set the post type to something that actually exists so the query doesn't abort early
		$query_args = array( 'post_type' => 'post', 'search_filter_id' => $sfid );
		
		return $query_args;
	}
	
	/**
	 * Add a S&F class to the archive widgets on frontend
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function filter_archives_before_render($element){
		
		//$sf_widget_types = array('posts', 'portfolio', 'products');
		
		$sf_archive_element_types = array('archive-posts', 'wc-archive-products');
		
		if(in_array($element->get_name(), $sf_archive_element_types)){
			//then we are dealing with an archive element, which already works, just add the ajax results class
			
			//first check to see if there is a search form using this display method
			$current_sfid = $this->get_archive_sfid();
			
			if($current_sfid){
				$sfid = intval($current_sfid);
			
				$element_class = 'search-filter-results-'.$sfid;
				
				$args = array(
					'class' => array($element_class)
				);
				
				$element->add_render_attribute('_wrapper', $args);
			}
			
		}
	}
	
	/**
	 * Hack `found_posts` for posts + portfolio widgets
	 * 
	 * Takes a WP Query, and changes the `found_posts` value - this is a hack to get around some
	 * of Elementors issues with displaying "no results" messages for posts + products elements
	 *
	 * if found_posts === 0, the element will not get rendered - see -
	 * elementor-pro/modules/posts/skins/skin-base.php - render()
	 * which returns no output from render function - causing the whole widget not to be rendered
	 * at all - see -
	 * elementor/includes/base/widget-base.php - render_content() 
	 * which also returns early if `$widget_content` is empty, and happens before the wrapper divs/containers
	 * are created... we want to keep the wrappers to put our no results message and let Elementor
	 * load its layout scripts
	 * 
	 * 
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function adjust_elementor_query_found_posts($query){
		
		if( 0 === $query->found_posts ){
			
			$query->set("found_posts", 1);
			$query->found_posts = 1;
		}
		remove_action( 'elementor/query/query_results', array( $this, 'adjust_elementor_query_found_posts' ), 10 );
	}
	
	/**
	 * Display no results message + fix pagination
	 *
	 * First check to make sure the element is one that should be affected by S&F
	 * Then check to see if we REALLY had no results (check `$query->posts` not `$query->found_posts`)
	 * and attach the no results message.
	 * Also fix pagination issues caused by the prev/next buttons in Elementors pagination
	 * 
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function filter_posts_render($element_html, $element){
		$sf_element_types = array('posts', 'portfolio', 'woocommerce-products', 'ae-post-blocks');
		
		if ( ! in_array( $element->get_name(), $sf_element_types ) ) {
			return $element_html;
		}
			
		$element_data = $element->get_data();
		$element_settings = $element_data['settings'];
		
		if ( ! isset( $element_settings['search_filter_query'] ) ) {
			return $element_html;
		}
		
		if ( ( 'posts' === $element->get_name() ) || ( 'portfolio' === $element->get_name() ) ) {
			
			if( !isset( $element_settings[ 'posts_post_type' ]) ) {
				return $element_html;
			}
			
			if( 'search_filter_query' !== $element_settings[ 'posts_post_type' ] ) {
				return $element_html;
			}
			
			$query = $element->get_query();
			
			// if $query->found_posts is 1, but the $query->posts array is empty (so found posts should be 0)
			// then its our hack from earlier, to get elementor to display the element markup with without content
			// when there are no results
			
			// add no results message
			if ( ( $query->found_posts==1 ) && ( 0 === count( $query->posts ) ) ) {
				//then there were no results, show the no results message
				$element_html = '';
				if ( isset( $element_settings[ 'search_filter_no_results' ] ) ){

					/* NOTICE - do not use the filter `search_filter_elementor_no_results_text` - it will be deprecated */
					$no_results_message = apply_filters( 'search_filter_elementor_no_results_text', esc_html( $element_settings[ 'search_filter_no_results' ] ), $element_settings['search_filter_query'] );
					$element_html = wp_kses_post( '<span data-search-filter-action="infinite-scroll-end">' . $no_results_message . '</span>' );
				}

			} else {
				//there could be pagination, so fix the prev / next nav links from elementor (they don't provide hooks, so we need to replace the html)
				$pagination_types_with_prev_next = array('prev_next', 'numbers_and_prev_next');	
				
				if(isset($element_settings['pagination_type'])){
					//check to make sure pagination is one of the types that shows the prev/next links
					if( in_array( $element_settings['pagination_type'], $pagination_types_with_prev_next ) ){
						$element_html = $this->fix_pagination_prev_next($element_html, $query);
					}
				}
			}

			ob_start();
			do_action( 'search_filter_elementor_widget_start_render', $element );
			$pre_render_html = ob_get_clean();
			ob_start();
			do_action( 'search_filter_elementor_widget_end_render', $element );
			$post_render_html = ob_get_clean();

			$element_html = $pre_render_html . $element_html . $post_render_html;
		}
		else if( 'ae-post-blocks' === $element->get_name() ){
			/* if( !isset( $element_settings[ 'ae_post_type' ]) ) {
				return $element_html;
			}
			
			if( 'search_filter_query' !== $element_settings[ 'ae_post_type' ] ) {
				return $element_html;
			}*/

		}
		else if( 'woocommerce-products' === $element->get_name() ){
			
			if( !isset( $element_settings[ 'query_post_type' ]) ) {
				return $element_html;
			}
			
			if( 'search_filter_query' !== $element_settings[ 'query_post_type' ] ) {
				return $element_html;
			}
			
			
			/*
			// this is where we would also fix the products shortcode pagination, however, it doesn't have pagination
			// so can leave out for now
			
			$query = $element->get_query();
			
			//if $query->found_posts is 1, but the $query->posts array is empty (so found posts should be 0)
			//the its our hack from earlier, to get elementor to display the element markup with wihtout content
			//when there are no results
			
			//add no results message
			if ( ( $query->found_posts==1 ) && ( 0 === count($query->posts) ) ){
				//then there were no results, show the no results message
				$element_html = "No Results Found";
			}
			else{
				//there could be pagination, so fix the prev / next nav links from elementor (they don't provide hooks, so we need to replace the html)
				
				$pagination_types_with_prev_next = array('prev_next', 'numbers_and_prev_next');	
				
				//check to make sure pagination is one of the types that shows the prev/next links
				if( in_array( $element_settings['pagination_type'], $pagination_types_with_prev_next ) ){
					$element_html = $this->fix_pagination_prev_next($element_html, $query);
				}
			}*/
		}
		
		return $element_html;
	}
	
	/**
	 * Takes a pagination link (anchor tag) and fixes the URLs for S&F
	 *
	 * Takes the rendered output, and replaces the prev/next pagination hrefs with ones that will work
	 * with S&F ( + we add the `paginate_links` filter back in )
	 * 
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function fix_pagination_prev_next($element_html, $query){
		
		//generate an assoc array prev/next links				
		$big = 999999999; // need an unlikely integer
		$prev_next_urls = $this->get_prev_next_pagination( array(
			'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format' => '?paged=%#%',
			'current' => max( 1, $query->get( 'paged' ) ),
			'total' => $query->max_num_pages
		) );
		
		//make sure there is a next URL
		if(!empty($prev_next_urls['next'])){
			
			//now try to find an existing one
			preg_match('/<a class="page-numbers next" href="[^>]*">(.*?)<\/a>/s', $element_html, $matched_next_link);
			$next_link = '';
			
			if(!empty($matched_next_link)){
				
				$next_link = $matched_next_link[0];
				$pattern = "/(?<=href=(\"|'))[^\"']+(?=(\"|'))/";
				$new_link = wp_kses_post( preg_replace($pattern, $prev_next_urls['next'], $next_link ) ); 
				$element_html = str_replace($next_link, $new_link, $element_html);
			}
		}
		
		//make sure there is a prev URL
		if(!empty($prev_next_urls['prev'])){
			
			//now try to find an existing one
			preg_match('/<a class="page-numbers prev" href="[^>]*">(.*?)<\/a>/s', $element_html, $matched_prev_link);
			$prev_link = '';
			
			if(!empty($matched_prev_link)){
				
				$prev_link = $matched_prev_link[0];
				$pattern = "/(?<=href=(\"|'))[^\"']+(?=(\"|'))/";
				$new_link = wp_kses_post( preg_replace( $pattern, $prev_next_urls['prev'], $prev_link) );  
				$element_html = str_replace($prev_link, $new_link, $element_html);
			}
		}
		
		return $element_html;
	}
	
	/**
	 * Add a S&F class to the products widget on frontend
	 * 
	 * Add a class to the products widget on frontend, as well as hooking into the products
	 * shortcode query to attach S&F
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function before_render_products_element( $element ) {
		
		$element_data = $element->get_data();
		
		if(!isset($element_data['widgetType'])){
			return; 
		}
		
		if($element_data['widgetType'] !== 'woocommerce-products'){
			return;
		}
		
		if(!isset($element_data['settings'])){
			return; 
		}
		
		$settings = $element_data['settings'];
		
		if( !isset( $settings [ 'query_post_type' ] ) ) {
			return;
		}
		
		if( 'search_filter_query' !== $settings[ 'query_post_type' ] ) {
			return;
		}
		
		if(!isset($settings['search_filter_query'])){
			return;
		}
		
		//don't start attaching things if another query is already open
		if ( 0 !== $this->current_products_query_id ){
			return;
		}
		
		$this->current_products_query_id =  intval($settings['search_filter_query']);

		if(isset($settings['search_filter_no_results'])){
			$this->wc_no_results_message = $settings['search_filter_no_results'];
		}
		
		//add results class to element
		$element->add_render_attribute( '_wrapper', [
			'class' => array('search-filter-results-'.$this->current_products_query_id),
			//'data-my_data' => 'my-data-value',
		] );
		
		//add filter to modify the WC shortcode query
		add_filter( 'woocommerce_shortcode_products_query', array( $this, 'attach_sf_to_wc_shortcode_args' ), 1000, 3 );
		
		//adjust total results to ensure Elementor generates the container markup for the element
		add_filter("woocommerce_shortcode_products_query_results", array($this, 'adjust_wc_query_total'), 10);
		
		//add the no results message, never fired without the above total being set
		add_action("woocommerce_shortcode_products_loop_no_results", array($this, 'add_wc_no_results'), 10);
		
	}
	
	/*
	 * Hack Elementor to bypass checks and still display the element
	 * 
	 * Trick Elementor in to thinking there is at least 1 result so it continues normal output of the element - which means, we can insert a "no results" message
	 * Very similar to  - adjust_elementor_query_found_posts
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function adjust_wc_query_total($results){
		
		if( empty( $results->total ) ){
			$results->total = 1;
		}
		remove_filter("woocommerce_shortcode_products_query_results", array($this, 'adjust_wc_query_total'), 10);
		return $results;
	}
	
	/*
	 * Add "no results" message
	 * 
	 * Now Elementor has been tricked into thinking there are results this hook is actually fired by WC
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function add_wc_no_results(){

		//then there were no results, show the no results message
		
		/* NOTICE - do not use the filter `search_filter_elementor_no_results_text` - it will be deprecated */
		$no_results_message = apply_filters( 'search_filter_elementor_no_results_text', esc_html( $this->wc_no_results_message ), $this->current_products_query_id );
		
		echo '<span data-search-filter-action="infinite-scroll-end">' . $no_results_message . '</span>';
		$this->wc_no_results_message = '';
		
		remove_action("woocommerce_shortcode_products_loop_no_results", array($this, 'add_wc_no_results'), 10);
	}
	/**
	 * Reset filters / values from products widget 
	 * 
	 * After the element has been rendered, remove the products shortcode filter for attaching S&F
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function after_render_products_element( $element ) {
		
		
		$element_data = $element->get_data();
		
		if(!isset($element_data['widgetType'])){
			return; 
		}
		
		if($element_data['widgetType'] !== 'woocommerce-products'){
			return;
		}
		
		if(!isset($element_data['settings'])){
			return; 
		}
		
		$settings = $element_data['settings'];
		
		if( !isset( $settings [ 'query_post_type' ] ) ) {
			return;
		}
		
		if( 'search_filter_query' !== $settings[ 'query_post_type' ] ) {
			return;
		}
		
		if(!isset($settings['search_filter_query'])){
			return;
		}
		
		remove_filter( 'woocommerce_shortcode_products_query', array( $this, 'attach_sf_to_wc_shortcode_args' ), 1000, 3 );
		$this->current_products_query_id = 0; //reset
	}
	
	
	/**
	 *
	 * If the current page is an archive, and is being affected by S&F, get the Search & Filter ID
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	
	private function get_archive_sfid(){
		
		//much simpler, return simply if its in, not under a bunch of awkward conditions
		global $wp_query;
		$search_filter_id = $wp_query->get('search_filter_id');
		if ( $search_filter_id ) { 
			return $search_filter_id;
		}
		
		return false;
	}
	/**
	 * Setup S&F form attributes 
	 * 
	 * Auto Setup ajax variables for search forms that are set to use Elementor widgets
	 * for results
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function search_filter_form_attributes($attributes, $sfid){
		
		if(isset($attributes['data-display-result-method']))
		{
			if( 1 == $attributes['data-ajax'] ) {
				
				if($attributes['data-display-result-method']=="elementor_posts_element") {
					$attributes['data-ajax-target'] = '.search-filter-results-'.$sfid;
					$attributes['data-ajax-links-selector'] = '.search-filter-results-'.$sfid.' a.page-numbers';
				}
				else {
					//only update archive settings if it is an elementor archive
					if( $this->is_elementor_archive() ) {
						if($attributes['data-display-result-method']=="custom_woocommerce_store"){
							$attributes['data-ajax-target'] = '.search-filter-results-'.$sfid;
							$attributes['data-ajax-links-selector'] = '.search-filter-results-'.$sfid.' a.page-numbers';
						}
						else if($attributes['data-display-result-method']=="post_type_archive"){
							$attributes['data-ajax-target'] = '.search-filter-results-'.$sfid;
							$attributes['data-ajax-links-selector'] = '.search-filter-results-'.$sfid.' a.page-numbers';
						}
					}
				}
			}
		}
		return $attributes;
	}

	public function is_elementor_page(){

		global $post;

		if ( $post ){
			$post_id = $post->ID;
			return \Elementor\Plugin::$instance->db->is_built_with_elementor( get_queried_object_id($post->ID) );
		}
		
		return false;
		
	}
	
	public function is_elementor_archive(){
		
		$location = 'archive';
		if ( class_exists('\ElementorPro\Plugin') ) {
			$conditions_manager = \ElementorPro\Plugin::instance()->modules_manager->get_modules('theme-builder')->get_conditions_manager();
			$documents_for_archive = $conditions_manager->get_documents_for_location( $location );
			
			if ( ! empty( $documents_for_archive ) ) {
				return true;
			}
		}

		return false;
	}
	
	/**
	 * Add a Elementor Display Method to search form admin
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function search_filter_admin_option_display_results($display_results_options){
		
		$display_results_options['elementor_posts_element'] = array(
            'label'         => __('Elementor Posts / Portfolio / Products', 'search-filter-elementor'),
            'description'   => 
				'<p>'.__('Search Results will displayed using one of Elementors <strong>Posts</strong> or <strong>Portfolio</strong> Elements.', 'search-filter-elementor').'</p>'.
				'<p>'.__('Remember to set the <strong>Content Source</strong> in your Elementor Module to use this Search Form.', 'search-filter-elementor').'</p>'.
				'<p>'.__('If you want to filter a <strong>Post Type Archive</strong> or the <strong>WooCommerce Shop / Products Archive</strong>, use those display methods instead.', 'search-filter-elementor').'</p>',
            'base'          => 'shortcode'
        );
		
		return $display_results_options;
	}
	
	/**
	 * Get array of pagination URLs
	 * 
	 * Modified version of `paginate_links` function, which will only return
	 * an array of prev/next urls
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	private function get_prev_next_pagination( $args = '' ) {
		
		//customised version of `paginate_links` which only returns prev/next urls
		global $wp_query, $wp_rewrite;

		// Setting up default values based on the current URL.
		$pagenum_link = html_entity_decode( get_pagenum_link() );
		$url_parts    = explode( '?', $pagenum_link );
		
		$defaults = array(
			'base'               => '', //must supply
			'format'             => '', //must supply
			'total'              => '', //must supply
			'current'            => '', //must supply
			'aria_current'       => 'page',
			'show_all'           => false,
			'prev_next'          => true,
			'prev_text'          => __( '&laquo; Previous' ),
			'next_text'          => __( 'Next &raquo;' ),
			'end_size'           => 1,
			'mid_size'           => 2,
			//'type'               => 'plain', //not needed
			'add_args'           => array(), // array of query args to add
			'add_fragment'       => '',
			'before_page_number' => '',
			'after_page_number'  => '',
		);

		$args = wp_parse_args( $args, $defaults );

		if ( ! is_array( $args['add_args'] ) ) {
			$args['add_args'] = array();
		}

		// Merge additional query vars found in the original URL into 'add_args' array.
		if ( isset( $url_parts[1] ) ) {
			// Find the format argument.
			$format       = explode( '?', str_replace( '%_%', $args['format'], $args['base'] ) );
			$format_query = isset( $format[1] ) ? $format[1] : '';
			wp_parse_str( $format_query, $format_args );

			// Find the query args of the requested URL.
			wp_parse_str( $url_parts[1], $url_query_args );

			// Remove the format argument from the array of query arguments, to avoid overwriting custom format.
			foreach ( $format_args as $format_arg => $format_arg_value ) {
				unset( $url_query_args[ $format_arg ] );
			}

			$args['add_args'] = array_merge( $args['add_args'], urlencode_deep( $url_query_args ) );
		}

		// Who knows what else people pass in $args
		$total = (int) $args['total'];
		if ( $total < 2 ) {
			return;
		}
		
		$current  = (int) $args['current'];
		$add_args   = $args['add_args'];
		$page_links = array('prev' => '', 'next' => '');

		if ( $args['prev_next'] && $current && 1 < $current ) :
			$link = str_replace( '%_%', 2 == $current ? '' : $args['format'], $args['base'] );
			$link = str_replace( '%#%', $current - 1, $link );
			if ( $add_args ) {
				$link = add_query_arg( $add_args, $link );
			}
			$link .= $args['add_fragment'];
			$page_links['prev'] = apply_filters( 'paginate_links', $link );

		else:
			$page_links['prev'] = '';
		endif;
				

		if ( $args['prev_next'] && $current && $current < $total ) :
			$link = str_replace( '%_%', $args['format'], $args['base'] );
			$link = str_replace( '%#%', $current + 1, $link );
			if ( $add_args ) {
				$link = add_query_arg( $add_args, $link );
			}
			$link .= $args['add_fragment'];
			$page_links['next'] = apply_filters( 'paginate_links', $link );
			
		else:
			$page_links['next'] = '';
		endif;
		
		return $page_links;
		
	}
}

if( !class_exists( 'Search_Filter_Elementor_Plugin_Updater' ) ) {
	// load our custom updater
	include( dirname( __FILE__ ) . '/search-filter-elementor-plugin-updater.php' );
}


Search_Filter_Elementor_Extension::instance();

