<?php

/**
 * The file that defines the core plugin class
 *
 * @since      1.0.0
 *
 * @package    Wholesalex_WCFM
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, load dependencies
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wholesalex_WCFM
 */
class Wholesalex_WCFM {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wholesalex_WCFM_Loader    $loader    Maintains and registers all hooks for the plugin.
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
	 * Dependency Plugins
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array    $plugin    All Dependency Plugins
	 */
	public $plugins;

	/**
	 * Endpoints
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array    $plugin    All Dependency Plugins
	 */
	public $endpoints;


	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public area.
	 *
	 * @since    1.0.0
	 */

	public function __construct( $plugins = array() ) {
		if ( defined( 'WHOLESALEX_WCFM_VERSION' ) ) {
			$this->version = WHOLESALEX_WCFM_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wholesalex-wcfm-b2b-multivendor-marketplace';
		$this->plugins     = $plugins;

		$this->load_dependencies();

		$this->set_locale();

		$dependency_statuses = $this->check_required_plugins_status();
		if ( $this->is_dependency_pass( $dependency_statuses ) ) {
			$this->define_public_hooks();
		} else {
			// dependency check failed. show notice

			$this->define_notices( $dependency_statuses );
			add_action( 'wp_ajax_install_wholesalex', array( $this, 'wholesalex_installation_callback' ) );
		}

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wholesalex_WCFM_Loader. Orchestrates the hooks of the plugin.
	 * - Wholesalex_WCFM_i18n. Defines internationalization functionality.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wholesalex-wcfm-b2b-multivendor-marketplace-loader.php';

		/**
		 * The class responsible for defining internationalization functionality of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wholesalex-wcfm-b2b-multivendor-marketplace-i18n.php';
		/**
		 * The class responsible for public functionality of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/public/class-wholesalex-wcfm-b2b-multivendor-marketplace-public.php';

		$this->loader = new Wholesalex_WCFM_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wholesalex_WCFM_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wholesalex_WCFM_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the public/frontend functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wholesalex_WCFM_Public( $this->endpoints );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );


		$this->loader->add_filter( 'wholesalex_setting_fields', $plugin_public, 'wcfm_wholesalex_settings_field', 99 );

		$this->loader->add_filter( 'wcfm_query_vars', $plugin_public, 'add_wholesalex_endpoints' );

		$this->loader->add_filter( 'wcfm_endpoint_wholesalex-conversations_title', $plugin_public, 'add_conversation_endpoint_title' );
		$this->loader->add_filter( 'wcfm_menus', $plugin_public, 'add_wholesalex_endpoints_in_wcfm_menus' );
		$this->loader->add_action( 'wcfm_load_views', $plugin_public, 'load_wholesalex_endpoint_views', 99 );
		$this->loader->add_action( 'wcfm_load_scripts', $plugin_public, 'load_wholesalex_endpoint_scripts', 99 );

		// dynamic rules
		if ( function_exists( 'wholesalex' ) && 'yes' === wholesalex()->get_setting( 'wcfm_vendor_dynamic_rule_status', 'yes' ) ) {

			$this->loader->add_filter( 'wcfm_endpoint_wholesalex-dynamic-rules_title', $plugin_public, 'add_dynamic_rule_endpoint_title' );

			$this->loader->add_filter( 'dynamic_rules_restapi_permission_callback', $plugin_public, 'set_restapi_permission' );

			$this->loader->add_filter( 'wholesalex_save_dynamic_rules', $plugin_public, 'add_meta_on_vendor_created_dynamic_rules', 10, 2 );

			$this->loader->add_filter( 'wholesalex_dynamic_rules_rule_type_options', $plugin_public, 'dynamic_rule_types_for_vendors' );

			$this->loader->add_filter( 'wholesalex_dynamic_rules_product_filter_options', $plugin_public, 'dynamic_rules_product_filter_vendors' );

			$this->loader->add_filter( 'wholesalex_dynamic_rules_condition_options', $plugin_public, 'dynamic_rules_conditions_vendors' );

			$this->loader->add_filter( 'wholesalex_get_all_dynamic_rules', $plugin_public, 'get_vendors_dynamic_rules' );
		}
		

		// Rolewise Wholesale Price.
		if ( function_exists( 'wholesalex' ) && 'yes' === wholesalex()->get_setting( 'wcfm_vendor_rolewise_wholesalex_price', 'yes' ) ) {

			$this->loader->add_action( 'after_wcfm_products_manage_pricing_fields', $plugin_public, 'add_wholesalex_pricing' );
			$this->loader->add_action( 'wcfm_products_manage_variable_end', $plugin_public, 'add_variable_wholesalex_pricing' );
			$this->loader->add_filter( 'wcfm_product_manage_fields_variations', $plugin_public, 'add_variations_data' );

			$this->loader->add_action( 'after_wcfm_products_manage_meta_save', $plugin_public, 'save_wholesalex_data', 10, 2 );
			$this->loader->add_action( 'after_wcfm_product_variation_meta_save', $plugin_public, 'save_wholesalex_variation_data', 10, 4 );

		}

		/**
		 * WholesaleX Section Status.
		 */
		if ( function_exists( 'wholesalex' ) && 'yes' === wholesalex()->get_setting( 'wcfm_vendor_product_wholesalex_section_status', 'yes' ) ) {
			$this->loader->add_action( 'after_wcfm_products_manage_attribute', $plugin_public, 'wholesalex_section' );
		}

		// Conversations
		if ( function_exists( 'wholesalex' ) && 'yes'=== wholesalex()->get_setting( 'wcfm_vendor_conversation_status', 'yes' ) ) {

			$this->loader->add_action( 'wholesalex_new_conversation_form_before_type', $plugin_public, 'add_vendor_fields_in_conversation' );

			$this->loader->add_action( 'wholesalex_conversation_created', $plugin_public, 'add_vendor_as_recipient' );

			$this->loader->add_filter( 'wholesalex_conversation_my_account_columns', $plugin_public, 'add_vendor_columns' );

			$this->loader->add_action( 'wholesalex_conversation_my_account_default_column_values', $plugin_public, 'populate_vendor_column_data', 10, 2 );

			$this->loader->add_filter( 'wholesalex_get_conversations_args', $plugin_public, 'modify_conversations_args', 10, 2 );

			$this->loader->add_filter( 'wholesalex_conversation_restapi_permission_callback', $plugin_public, 'set_restapi_permission' );

			$this->loader->add_filter( 'wholesalex_addon_conversation_has_eligibility_to_view_conversation', $plugin_public, 'allow_vendor_to_view_conversation', 10, 3 );

			$this->loader->add_filter( 'wholesalex_addon_conversation_view_author_ids', $plugin_public, 'add_vendor_id_as_valid_post_author' );

			$this->loader->add_filter( 'wholesalex_addon_conversation_reply_class', $plugin_public, 'add_conversation_vendor_reply_class', 10, 3 );

		}

		// WholesaleX role Assign
		if ( function_exists( 'wholesalex' ) ) {
			$this->loader->add_filter('wcfmvm_registration_static_fields', $plugin_public, 'wholesalex_add_custom_registration_checkbox', 10, 1);
			$this->loader->add_action('wcfm_membership_registration', $plugin_public, 'wholesalex_wcfm_membership_registration_data', 10, 2);
			$this->loader->add_action('end_wcfm_membership_registration_form', $plugin_public, 'wholesalex_add_custom_select_markup' );
		}

	}

	/**
	 * Register all of the hooks related to the public/frontend functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_notices( $plugin_statuses ) {
		if ( is_admin() && current_user_can( 'activate_plugins' ) ) {
			foreach ( $plugin_statuses as $key => $value ) {
				switch ( $key ) {
					case 'WholesaleX':
						add_action( 'admin_notices', array( $this, 'wholesalex_intro_notice' ) );
						break;

					default:
						// code...
						break;
				}
			}
		}
	}


	public function wholesalex_intro_notice() {
		// check wholesalex is installed or not.
		$wholesalex_installed = file_exists( WP_PLUGIN_DIR . '/wholesalex/wholesalex.php' );

		$regular_text    = $wholesalex_installed ? esc_html__( 'Activate', 'wholesalex-wcfm-b2b-multivendor-marketplace' ) : esc_html__( 'Install', 'wholesalex-wcfm-b2b-multivendor-marketplace' );
		$processing_text = $wholesalex_installed ? esc_html__( 'Activating..', 'wholesalex-wcfm-b2b-multivendor-marketplace' ) : esc_html__( 'Installing..', 'wholesalex-wcfm-b2b-multivendor-marketplace' );
		$processed_text  = $wholesalex_installed ? esc_html__( 'Activated', 'wholesalex-wcfm-b2b-multivendor-marketplace' ) : esc_html__( 'Installed', 'wholesalex-wcfm-b2b-multivendor-marketplace' );

		if ( defined( 'WHOLESALEX_VER' ) && WHOLESALEX_VER ) {
			return;
		}
		?>
				<style>
					/*----- WholesaleX Into Notice ------*/
					.notice.notice-success.wholesalex-wcfm-b2b-multivendor-marketplace-wholesalex-notice {
						border-left-color: #4D4DFF;
						padding: 0;
					}

					.wholesalex-wcfm-b2b-multivendor-marketplace-notice-container {
						display: flex;
					}

					.wholesalex-wcfm-b2b-multivendor-marketplace-notice-container a{
						text-decoration: none;
					}

					.wholesalex-wcfm-b2b-multivendor-marketplace-notice-container a:visited{
						color: white;
					}

					.wholesalex-wcfm-b2b-multivendor-marketplace-notice-image {
						padding-top: 15px;
						padding-left: 12px;
						padding-right: 12px;
						background-color: #f4f4ff;
						max-width: 40px;
					}
					.wholesalex-wcfm-b2b-multivendor-marketplace-notice-image img{
						max-width: 100%;
					}

					.wholesalex-wcfm-b2b-multivendor-marketplace-notice-content {
						width: 100%;
						padding: 16px;
						display: flex;
						flex-direction: column;
						gap: 8px;
					}

					.wholesalex-wcfm-b2b-multivendor-marketplace-notice-wholesalex-button {
						max-width: fit-content;
						padding: 8px 15px;
						font-size: 16px;
						color: white;
						background-color: #4D4DFF;
						border: none;
						border-radius: 2px;
						cursor: pointer;
						margin-top: 6px;
						text-decoration: none;
					}

					.wholesalex-wcfm-b2b-multivendor-marketplace-notice-heading {
						font-size: 18px;
						font-weight: 500;
						color: #1b2023;
					}

					.wholesalex-wcfm-b2b-multivendor-marketplace-notice-content-header {
						display: flex;
						justify-content: space-between;
						align-items: center;
					}

					.wholesalex-wcfm-b2b-multivendor-marketplace-notice-close .dashicons-no-alt {
						font-size: 25px;
						height: 26px;
						width: 25px;
						cursor: pointer;
						color: #585858;
					}

					.wholesalex-wcfm-b2b-multivendor-marketplace-notice-close .dashicons-no-alt:hover {
						color: red;
					}

					.wholesalex-wcfm-b2b-multivendor-marketplace-notice-content-body {
						font-size: 14px;
						color: #343b40;
					}

					.wholesalex-wcfm-b2b-multivendor-marketplace-notice-wholesalex-button:hover {
						background-color: #6C6CFF;
						color: white;
					}

					span.wholesalex-wcfm-b2b-multivendor-marketplace-bold {
						font-weight: bold;
					}
					a.wholesalex-wcfm-b2b-multivendor-marketplace-wholesalex-pro-dismiss:focus {
						outline: none;
						box-shadow: unset;
					}
					.loading {
						width: 16px;
						height: 16px;
						border: 3px solid #FFF;
						border-bottom-color: transparent;
						border-radius: 50%;
						display: inline-block;
						box-sizing: border-box;
						animation: rotation 1s linear infinite;
						margin-left: 10px;
					}

					@keyframes rotation {
						0% {
							transform: rotate(0deg);
						}

						100% {
							transform: rotate(360deg);
						}
					}
					/*----- End WholesaleX Into Notice ------*/

				</style>
				<div class="notice notice-success wholesalex-wcfm-b2b-multivendor-marketplace-wholesalex-notice">
					<div class="wholesalex-wcfm-b2b-multivendor-marketplace-notice-container">
						<div class="wholesalex-wcfm-b2b-multivendor-marketplace-notice-image"><img src="<?php echo esc_url( WHOLESALEX_WCFM_URL ) . 'assets/img/wholesalex-icon.svg'; ?>"/></div>
						<div class="wholesalex-wcfm-b2b-multivendor-marketplace-notice-content">
							<div class="wholesalex-wcfm-b2b-multivendor-marketplace-notice-content-header">
								<div class="wholesalex-wcfm-b2b-multivendor-marketplace-notice-heading">
									<?php echo esc_html__( 'WholesaleX for WCFM needs the “WholesaleX” plugin to run.', 'wholesalex-wcfm-b2b-multivendor-marketplace' ); ?>
								</div>
							</div>
							<?php
							if ( current_user_can( 'install_plugins' ) ) {
								?>
								<a id="wholesalex-wcfm-b2b-multivendor-marketplace_install_wholesalex" class="wholesalex-wcfm-b2b-multivendor-marketplace-notice-wholesalex-button " ><?php echo esc_html( $regular_text ); ?></a>
								<?php
							}
							?>
						</div>
					</div>
				</div>

				<script>
					const installWholesaleX = (element)=>{
						element.innerHTML = "<?php echo esc_html( $processing_text ); ?> <span class='loading'></span>";
						const wholesalex_wcfm_ajax = "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>";
						const formData = new FormData();
						formData.append('action','install_wholesalex');
						formData.append('wpnonce',"<?php echo esc_attr( wp_create_nonce( 'install_wholesalex' ) ); ?>");
						fetch(wholesalex_wcfm_ajax, {
							method: 'POST',
							body: formData,
						})
						.then(res => res.json())
						.then(res => {
							if(res) {
								if (res.success ) {
									element.innerHTML = "<?php echo esc_html( $processed_text ); ?>";
								} else {
									console.log("installation failed..");
								}
							}
							location.reload();
						})
					}
					const wholesalex_wcfm_element = document.getElementById('wholesalex-wcfm-b2b-multivendor-marketplace_install_wholesalex');
					wholesalex_wcfm_element.addEventListener('click',(e)=>{
						e.preventDefault();
						installWholesaleX(wholesalex_wcfm_element);
					})
				</script>
			<?php
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
	 * @return    Wholesalex_WCFM_Loader    Orchestrates the hooks of the plugin.
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

	/**
	 * Check Dependency Plugin Installed or not
	 *
	 * @return boolean
	 */
	public function check_required_plugins_status() {
		$active_plugins = get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, array_keys( get_site_option( 'active_sitewide_plugins', array() ) ) );
		}

		$dependency_statuses = array();

		foreach ( $this->plugins as $key => $plugin ) {
			$is_exist                    = file_exists( WP_PLUGIN_DIR . '/' . $plugin['path'] );
			$is_active                   = $is_exist && in_array( $plugin['path'], $active_plugins, true );
			$dependency_statuses[ $key ] = array(
				'path'      => $plugin['path'],
				'is_exist'  => $is_exist,
				'is_active' => $is_active,
			);

		}

		return $dependency_statuses;
	}


	public function is_dependency_pass( $dependency_statuses ) {

		foreach ( $dependency_statuses as $key => $plugin ) {
			if ( ! $plugin['is_active'] ) {
				return false;
			}
		}
		return true && defined( 'WHOLESALEX_VER' ) && version_compare( WHOLESALEX_VER, '1.2.4', '>=' );
	}


	/**
	 * WholesaleX Installation Callback From Banner.
	 *
	 * @return void
	 */
	public function wholesalex_installation_callback() {
		if ( ! isset( $_POST['wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['wpnonce'] ) ), 'install_wholesalex' ) ) {
			wp_send_json_error( 'Nonce Verification Failed' );
			die();
		}

		$wholesalex_installed = file_exists( WP_PLUGIN_DIR . '/wholesalex/wholesalex.php' );

		if ( ! $wholesalex_installed ) {
			$status = $this->plugin_install( 'wholesalex' );
			if ( $status && ! is_wp_error( $status ) ) {
				$activate_status = activate_plugin( 'wholesalex/wholesalex.php', '', false, true );
				if ( is_wp_error( $activate_status ) ) {
					wp_send_json_error( array( 'message' => __( 'WholesaleX Activation Failed!', 'wholesalex-wcfm-b2b-multivendor-marketplace' ) ) );
				}
			} else {
				wp_send_json_error( array( 'message' => __( 'WholesaleX Installation Failed!', 'wholesalex-wcfm-b2b-multivendor-marketplace' ) ) );
			}
		} else {
			$is_wc_active = is_plugin_active( 'wholesalex/wholesalex.php' );
			if ( ! $is_wc_active ) {
				$activate_status = activate_plugin( 'wholesalex/wholesalex.php', '', false, true );
				if ( is_wp_error( $activate_status ) ) {
					wp_send_json_error( array( 'message' => __( 'WholesaleX Activation Failed!', 'wholesalex-wcfm-b2b-multivendor-marketplace' ) ) );
				}
			}
		}

		wp_send_json_success( __( 'Successfully Installed and Activated', 'wholesalex-wcfm-b2b-multivendor-marketplace' ) );

	}

	/**
	 * Plugin Install
	 *
	 * @param string $plugin Plugin Slug.
	 * @return boolean
	 * @since 2.6.1
	 */
	public function plugin_install( $plugin ) {

		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		$api = plugins_api(
			'plugin_information',
			array(
				'slug'   => $plugin,
				'fields' => array(
					'sections' => false,
				),
			)
		);

		if ( is_wp_error( $api ) ) {
			return $api->get_error_message();
		}

		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $api->download_link );

		return $result;
	}

}
