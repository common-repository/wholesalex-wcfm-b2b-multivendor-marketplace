<?php

/**
 * The public functionality of the plugin.
 *
 * @link       https://www.wpxpo.com/
 * @since      1.0.0
 *
 * @package    Wholesalex_WCFM
 */

 use WHOLESALEX\WHOLESALEX_Dynamic_Rules;
 use WHOLESALEX\WHOLESALEX_Product;
 use WHOLESALEX_PRO\AccountPage;
 use WHOLESALEX_PRO\Conversation;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * The public functionality of the plugin.
 */
class Wholesalex_WCFM_Public {

	public $endpoints;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $endpoints = array() ) {
		$this->endpoints = $endpoints;
	}


	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'wholesalex_wcfm', WHOLESALEX_WCFM_URL . 'assets/css/wholesalex-wcfm-public.css', array(), WHOLESALEX_WCFM_VERSION, 'all' );
	}

	/**
	 * Get WCFM Vendor ID
	 *
	 * @return string|int
	 * @since 1.0.0
	 */
	public function get_vendor_id() {
		$user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
		return $user_id;
	}

	/**
	 * Check Current User Is Valid WCFM Seller
	 *
	 * @return boolean
	 * @since 1.0.0
	 */
	public function is_seller() {
		$status = false;

		if ( function_exists( 'wcfm_is_vendor' ) && function_exists( 'wholesalex' ) ) {
			$status = wcfm_is_vendor( $this->get_vendor_id() );
		}

		return $status;
	}

	/**
	 * Check WholesaleX Pro and conversation is activated or not
	 *
	 * @return boolean
	 * @since 1.0.0
	 */
	public function is_conversation_active() {

		return defined( 'WHOLESALEX_PRO_VER' ) && version_compare( WHOLESALEX_PRO_VER, '1.2.3', '>=' ) && function_exists( 'wholesalex_pro' ) && 'yes' === wholesalex()->get_setting( 'wsx_addon_conversation' );
	}

	/**
	 * Add WholesaleX Endpoints to WCFM Frontend Manager
	 * Added Dynamic Rules and Conversation Endpoint in WCFM Frontend Manager Query Vars
	 *
	 * @param array $query_vars Query Vars.
	 * @return array
	 * @since 1.0.0
	 */
	public function add_wholesalex_endpoints( $query_vars ) {

		if ( function_exists( 'wholesalex' ) && wholesalex()->get_setting( 'wcfm_vendor_dynamic_rule_status', 'yes' ) ) {
			$query_vars['wholesalex-dynamic-rules'] = 'wholesalex-dynamic-rules';
		}

		if ( function_exists( 'wholesalex' ) && wholesalex()->get_setting( 'wcfm_vendor_conversation_status', 'yes' ) && $this->is_conversation_active() ) {
			$query_vars['wholesalex-conversations'] = 'wholesalex-conversations';
		}
		return $query_vars;
	}

	/**
	 * Add WholesaleX Dynamic Rule Endpoints Title to WCFM Frontend Manager
	 * Added WholesaleX Dynamic Rules Endpoint Title in WCFM Frontend Manager.
	 *
	 * @param array $endpoint Default Endpoint Title.
	 * @return array
	 * @since 1.0.0
	 */
	public function add_dynamic_rule_endpoint_title( $endpoint ) {

		if ( function_exists( 'wholesalex' ) && 'yes' === wholesalex()->get_setting( 'wcfm_vendor_dynamic_rule_status', 'yes' ) ) {
			$endpoint = __( 'Dynamic Rules', 'wholesalex-wcfm-b2b-multivendor-marketplace' );
		}
		return $endpoint;
	}

	/**
	 * Set WholesaleX RestAPI Permission
	 *
	 * By Default Only The user who has manage_users capability, he can get restapi permission.
	 * If current user is wcfm seller, then we allow restapi permission for this user.
	 *
	 * @param boolean $status WholesaleX RestAPI Permission
	 * @return boolean
	 * @since 1.0.0
	 */
	public function set_restapi_permission( $status ) {
		return $status || $this->is_seller();
	}
	/**
	 * Add WholesaleX Conversation Endpoint Title to WCFM Frontend Manager
	 * Added WholesaleX Conversation Endpoint Title in WCFM Frontend Manager.
	 *
	 * @param array $endpoint Default Endpoint Title.
	 * @return array
	 * @since 1.0.0
	 */
	public function add_conversation_endpoint_title( $endpoint ) {
		if ( function_exists( 'wholesalex' ) && 'yes' === wholesalex()->get_setting( 'wcfm_vendor_conversation_status', 'yes' ) ) {
			$endpoint = __( 'Conversations', 'wholesalex-wcfm-b2b-multivendor-marketplace' );
		}

		return $endpoint;
	}


	public function add_wholesalex_endpoints_in_wcfm_menus( $menus ) {
		$wcfm_page = get_wcfm_page();
		if ( 'yes' === wholesalex()->get_setting( 'wcfm_vendor_dynamic_rule_status', 'yes' ) ) {
			$menus['wholesalex-dynamic-rules'] = array(
				'label'    => __( 'Dynamic Rules', 'wholesalex-wcfm-b2b-multivendor-marketplace' ),
				'url'      => wcfm_get_endpoint_url( 'wholesalex-dynamic-rules', '', $wcfm_page ),
				'icon'     => 'fa-layer-group',
				'priority' => 43,
			);
		}
		if ( 'yes' === wholesalex()->get_setting( 'wcfm_vendor_conversation_status', 'yes' ) && $this->is_conversation_active() ) {
			$menus['wholesalex-conversations'] = array(
				'label'    => __( 'Conversations', 'wholesalex-wcfm-b2b-multivendor-marketplace' ),
				'url'      => wcfm_get_endpoint_url( 'wholesalex-conversations', '', $wcfm_page ),
				'icon'     => 'fa-comments',
				'priority' => 44,
			);
		}

		return $menus;
	}


	/**
	 * Add WCFM Related Settings on WholesaleX Settings Page
	 *
	 * @param array $fields Settings Fields.
	 * @return array
	 */
	public function wcfm_wholesalex_settings_field( $fields ) {
		$settings = array(
			'wcfm_wholesalex' => array(
				'label' => __( 'WCFM Integration', 'wholesalex' ),
				'attr'  => array(
					'wcfm_vendor_dynamic_rule_status' => array(
						'type'    => 'switch',
						'label'   => __( 'Dynamic Rules', 'wholesalex' ),
						'desc'    => __( 'Enable Dynamic Rule feature for vendors.', 'wholesalex' ),
						'default' => 'yes',
					),
					'wcfm_vendor_rolewise_wholesalex_price' => array(
						'type'    => 'switch',
						'label'   => __( 'Role-Based Pricing', 'wholesalex' ),
						'desc'    => __( 'Let vendors add wholesale prices based on user roles.', 'wholesalex' ),
						'default' => 'yes',
					),
					'wcfm_vendor_product_wholesalex_section_status' => array(
						'type'    => 'switch',
						'label'   => __( 'WholesaleX Options', 'wholesalex' ),
						'desc'    => __( ' Enable WholesaleX options on product editing page.', 'wholesalex' ),
						'default' => 'yes',
					),

				),
			),
		);

		$fields = wholesalex()->insert_into_array( $fields, $settings );

		if ( $this->is_conversation_active() ) {
			if ( isset( $fields['wcfm_wholesalex'], $fields['wcfm_wholesalex']['attr'] ) && is_array( $fields['wcfm_wholesalex']['attr'] ) ) {
				$fields['wcfm_wholesalex']['attr']['wcfm_vendor_conversation_status'] = array(
					'type'    => 'switch',
					'label'   => __( 'Conversation', 'wholesalex' ),
					'desc'    => __( 'Enable WholesaleX conversation feature for vendors and marketplace admin.', 'wholesalex' ),
					'default' => 'yes',
				);
			}
		}

		return $fields;
	}


	public function load_wholesalex_endpoint_views( $endpoint ) {
		switch ( $endpoint ) {
			case 'wholesalex-dynamic-rules':
				if ( 'yes' === wholesalex()->get_setting( 'wcfm_vendor_dynamic_rule_status', 'yes' ) ) {
					?>
						<div class="collapse wcfm-collapse" id="wcfm_wholesalex_dynamic_rules"> 
						<div class="wcfm-page-headig">

								<span class="wcfmfa wholesalex_wcfm_logo"><img src="<?php echo esc_url(WHOLESALEX_WCFM_URL) . 'assets/img/wholesalex_white.svg'; ?>" class=" wholesalex_logo "> </span>
								 
								<span class="wcfm-page-heading-text"><?php echo esc_html__( 'Dynamic Rules', 'wholesalex-wcfm-b2b-multivendor-marketplace' ); ?></span>

							<?php do_action( 'wcfm_page_heading' ); ?>
						</div>
							<div class="wcfm-collapse-content">
								<div id="wcfm_page_load"></div>
								<?php $this->dynamic_rules_content(); ?>
							</div>
						</div>

					<?php
				}

				break;
			case 'wholesalex-conversations':
				if ( 'yes' === wholesalex()->get_setting( 'wcfm_vendor_conversation_status', 'yes' ) ) {
					?>
					<div class="collapse wcfm-collapse" id="wcfm_wholesalex_conversations"> 
					<div class="wcfm-page-headig">

							<span class="wcfmfa wholesalex_wcfm_logo"><img src="<?php echo esc_url(WHOLESALEX_WCFM_URL) . 'assets/img/wholesalex_white.svg'; ?>" class=" wholesalex_logo "> </span>
							 
							<span class="wcfm-page-heading-text"><?php echo esc_html__( 'Conversations', 'wholesalex-wcfm-b2b-multivendor-marketplace' ); ?></span>

						<?php do_action( 'wcfm_page_heading' ); ?>
					</div>
						<div class="wcfm-collapse-content">
							<div id="wcfm_page_load"></div>
							<?php $this->conversations_content(); ?>
						</div>
					</div>

					<?php
				}

			default:
				// code...
				break;
		}
	}

	public function load_wholesalex_endpoint_scripts( $endpoint ) {
		$wcfm_page = get_wcfm_page();

		switch ( $endpoint ) {
			case 'wholesalex-dynamic-rules':
				if ( 'yes' === wholesalex()->get_setting( 'wcfm_vendor_dynamic_rule_status', 'yes' ) ) {
					wp_enqueue_script( 'wholesalex_dynamic_rules' );
					$__dynamic_rules = array_values( wholesalex()->get_dynamic_rules_by_user_id() );
					$__dynamic_rules = apply_filters( 'wholesalex_get_all_dynamic_rules', array_values( $__dynamic_rules ) );

					if ( empty( $__dynamic_rules ) ) {
						$__dynamic_rules = array(
							array(
								'id'    => floor( microtime( true ) * 1000 ),
								'label' => __( 'New Rule', 'wholesalex-wcfm-b2b-multivendor-marketplace' ),
							),
						);
					}
					wp_localize_script(
						'wholesalex_dynamic_rules',
						'whx_dr',
						apply_filters(
							'wholesalex_wcfm_dynamic_rules_localize_data',
							array(
							'fields' => WHOLESALEX_Dynamic_Rules::get_dynamic_rules_field(),
							'rule'   => $__dynamic_rules,
							'i18n'   => array(
							'dynamic_rules' => __('Dynamic Rules', 'wholesalex'),
							'please_fill_all_fields' => __('Please Fill All Fields.', 'wholesalex'),
							'minimum_product_quantity_should_greater_then_free_product_qty' => __('Minimum Product Quantity Should Greater then Free Product Quantity.', 'wholesalex'),
							'rule_title' => __('Rule Title', 'wholesalex'),
							'create_dynamic_rule' => __('Create Dynamic Rule', 'wholesalex'),
							'import' => __('Import', 'wholesalex'),
							'export' => __('Export', 'wholesalex'),
							'untitled' => __('Untitled', 'wholesalex'),
							'duplicate_of' => __('Duplicate of ', 'wholesalex'),
							'delete_this_rule' => __('Delete this Rule.', 'wholesalex'),
							'duplicate_this_rule' => __('Duplicate this Rule.', 'wholesalex'),
							'show_hide_rule_details' => __('Show/Hide Rule Details.', 'wholesalex'),
							'vendor' => __('Vendor #', 'wholesalex'),
							'untitled_rule' => __('Untitled Rule', 'wholesalex'),
							'error_occured' => __('Error Occured!', 'wholesalex'),
							'map_csv_fields_to_dynamic_rules' => __('Map CSV Fields to Dynamic Rules', 'wholesalex'),
							'select_field_from_csv_msg' => __('Select fields from your CSV file to map against role fields, or to ignore during import.', 'wholesalex'),
							'column_name' => __('Column name', 'wholesalex'),
							'map_to_field' => __('Map to field', 'wholesalex'),
							'do_not_import' => __('Do not import', 'wholesalex'),
							'run_the_importer' => __('Run the importer', 'wholesalex'),
							'importing' => __('Importing', 'wholesalex'),
							'upload_csv' => __('Upload CSV', 'wholesalex'),
							'you_can_upload_only_csv_file_format' => __('You can upload only csv file format', 'wholesalex'),
							'your_dynamic_rules_are_now_being_importing' => __('Your Dynamic Rules are now being imported..', 'wholesalex'),
							'update_existing_rules' => __('Update Existing Rules', 'wholesalex'),
							'select_update_exising_rule_msg' => __('Selecting "Update Existing Rules" will only update existing rules. No new rules will be added.', 'wholesalex'),
							'continue' => __('Continue', 'wholesalex'),
							'dynamic_rule_imported' => __(' Dynamic Rules Imported.', 'wholesalex'),
							'dynamic_rule_updated' => __(' Dynamic Rules Updated.', 'wholesalex'),
							'dynamic_rule_skipped' => __(' Dynamic Rules Skipped.', 'wholesalex'),
							'dynamic_rule_failed' => __(' Dynamic Rules Failed.', 'wholesalex'),
							'view_error_logs' => __('View Error Logs', 'wholesalex'),
							'dynamic_rule' => __('Dynamic Rule', 'wholesalex'),
							'reason_for_failure' => __('Reason for failure', 'wholesalex'),
							'import_dynamic_rules' => __('Import Dynamic Rules', 'wholesalex'),
							)
							)
						)
					);
				}

				break;
			case 'wholesalex-conversations':
				if ( 'yes' === wholesalex()->get_setting( 'wcfm_vendor_conversation_status', 'yes' ) ) {
					wp_enqueue_script( 'whx_conversation' );
					wp_enqueue_script( 'wholesalex_node_vendors' );
					wp_enqueue_script( 'wholesalex_components' );

					$heading_data = array();

					// Prepare as heading data
					foreach ( Conversation::get_wholesalex_conversation_columns() as $key => $value ) {
						$data               = array();
						$data['all_select'] = '';
						$data['name']       = $key;
						$data['title']      = $value;
						if ( 'action' == $key ) {
							$data['type'] = '3dot';
						} else {
							$data['type'] = 'text';
						}

						$heading_data[ $key ] = $data;
					}

					$heading_data['title']['status']  = 'yes';
					$heading_data['user']['status']   = 'yes';
					$heading_data['status']['status'] = 'yes';
					$heading_data['type']['status']   = 'yes';
					$heading_data['email']['status']  = 'yes';
					$heading_data['action']['status'] = 'yes';

					wp_localize_script(
						'whx_conversation',
						'whx_conversation',
						apply_filters(
							'wholesalex_wcfm_conversation_localize_data',
							array(
									'heading'      => $heading_data,
									'bulk_actions' => wholesalex()->insert_into_array(Conversation::get_conversation_bulk_action(), array('' => __('Bulk Actions', 'wholesalex-wcfm-b2b-multivendor-marketplace')), 0),
									'statuses'     => wholesalex()->insert_into_array(
										Conversation::get_conversation_status(),
										array('' => __('Select Status', 'wholesalex-wcfm-b2b-multivendor-marketplace')),
										0
									),
							'types'                => wholesalex()->insert_into_array(
								Conversation::get_conversation_types(),
								array('' => __('Select Type', 'wholesalex-wcfm-b2b-multivendor-marketplace')),
								0
							),
							'new_conversation_url' => admin_url('post-new.php?post_type=wsx_conversation'),
							'post_statuses'        => Conversation::get_post_statuses(),
							'frontend_url'         => wcfm_get_endpoint_url('wholesalex-conversations', '', $wcfm_page),
							'conversation_per_page' => 10,
							'i18n'                 => array(
								'edit'                   => __('Edit','wholesalex-wcfm-b2b-multivendor-marketplace'),
								'resolved'               => __('Resolved','wholesalex-wcfm-b2b-multivendor-marketplace'),
								'force_delete'           => __('Force Delete','wholesalex-wcfm-b2b-multivendor-marketplace'),
								'move_to_trash'          => __('Move To Trash','wholesalex-wcfm-b2b-multivendor-marketplace'),
								'selected_conversations' => __('Selected Conversations','wholesalex-wcfm-b2b-multivendor-marketplace'),
								'add_new_conversation'   => __('Add New Conversation','wholesalex-wcfm-b2b-multivendor-marketplace'),
								'apply'                  => __('Apply','wholesalex-wcfm-b2b-multivendor-marketplace'),
								'columns'                => __('Columns','wholesalex-wcfm-b2b-multivendor-marketplace'),
								'no_conversations_found' => __('No Conversations Found!','wholesalex-wcfm-b2b-multivendor-marketplace'),
								'showing'                => __('Showing','wholesalex-wcfm-b2b-multivendor-marketplace'),
								'pages'                  => __('Pages','wholesalex-wcfm-b2b-multivendor-marketplace'),
							)
							)
							
						)
					);
				}
				break;

			default:
				// code...
				break;
		}

	}

	/**
	 * Set Dynamic Rules Page Content on Vendor Dashboard
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function dynamic_rules_content() {
		?>
		<div id="_wholesalex_dynamic_rules_frontend" class="wholesalex_wcfm"></div>
		<?php
	}

	/**
	 * Add a key to the dynamic rules, which is created by vendors
	 * This key is used to determine which dynamic rules is created by vendor
	 *
	 * @param array   $rule Dynamic Rule.
	 * @param boolean $is_frontend If the request comes from frontend/any vendor dashboard
	 * @return void
	 * @since 1.0.0
	 */
	public function add_meta_on_vendor_created_dynamic_rules( $rule, $is_frontend ) {

		if ( $is_frontend ) {
			$rule['created_from'] = wcfm_is_vendor( $this->get_vendor_id() ) ? 'vendor_dashboard' : '';
		}

		return $rule;
	}

	/**
	 * Set Dynamic Rule Types for Vendors
	 * All Dynamic Rules does not work for vendor, here specifiy which dynamic rules are available for vendors
	 *
	 * @param array $rule_types Rule Types
	 * @return array
	 * @since 1.0.0
	 */
	public function dynamic_rule_types_for_vendors( $rule_types ) {
		global $WCFM_Query;

		if ( 'wholesalex-dynamic-rules' === $WCFM_Query->get_current_endpoint() && $this->is_seller() ) {
			if ( isset( $rule_types['cart_discount'] ) ) {
				unset( $rule_types['cart_discount'] );
			}
			if ( isset( $rule_types['payment_discount'] ) ) {
				unset( $rule_types['payment_discount'] );
			}
			if ( isset( $rule_types['payment_order_qty'] ) ) {
				unset( $rule_types['payment_order_qty'] );
			}
			if ( isset( $rule_types['extra_charge'] ) ) {
				unset( $rule_types['extra_charge'] );
			}
			if ( isset( $rule_types['pro_extra_charge'] ) ) {
				unset( $rule_types['pro_extra_charge'] );
			}
			if ( isset( $rule_types['pro_restrict_product_visibility'] ) ) {
				unset( $rule_types['pro_restrict_product_visibility'] );
			}
			if ( isset( $rule_types['restrict_product_visibility'] ) ) {
				unset( $rule_types['restrict_product_visibility'] );
			}
		}

		return $rule_types;
	}

	/**
	 * Set Dynamic Rule Filter for Vendors
	 * All Dynamic Rules Filter does not work for vendor, here specifiy which dynamic rules filter are available for vendors
	 *
	 * @param array $options Dynamic Rules Filter Options
	 * @return array
	 * @since 1.0.0
	 */
	public function dynamic_rules_product_filter_vendors( $options ) {
		global $WCFM_Query;

		if ( 'wholesalex-dynamic-rules' === $WCFM_Query->get_current_endpoint() && $this->is_seller() ) {
			if ( isset( $options['all_products'] ) ) {
				unset( $options['all_products'] );
			}
			if ( isset( $options['cat_in_list'] ) ) {
				unset( $options['cat_in_list'] );
			}
			if ( isset( $options['cat_not_in_list'] ) ) {
				unset( $options['cat_not_in_list'] );
			}
		}

		return $options;
	}

	/**
	 * Set Dynamic Rule Conditions for Dokan Vendors
	 * All Dynamic Rules Conditions does not work for vendor, here specifiy which dynamic rules Conditions are available for vendors
	 *
	 * @param array $options Dynamic Rules Conditions Options
	 * @return array
	 * @since 1.0.0
	 */
	public function dynamic_rules_conditions_vendors( $options ) {
		global $WCFM_Query;

		if ( 'wholesalex-dynamic-rules' === $WCFM_Query->get_current_endpoint() && $this->is_seller() ) {
			if ( isset( $options['cart_total_qty'] ) ) {
				unset( $options['cart_total_qty'] );
			}
			if ( isset( $options['cart_total_value'] ) ) {
				unset( $options['cart_total_value'] );
			}
			if ( isset( $options['cart_total_weight'] ) ) {
				unset( $options['cart_total_weight'] );
			}
		}

		return $options;
	}


	/**
	 * Get Vendor Dynamic Rules
	 *
	 * @param array $rules Dynamic Rules
	 * @since 1.0.0
	 * @return array
	 */
	public function get_vendors_dynamic_rules( $rules ) {
		global $WCFM_Query;

		// Check is in wcfm seller dashboard
		// If current page is wcfm seller dashboard, return all dynamic rules which is created by the vendor and its stuff.
		if ( 'wholesalex-dynamic-rules' === $WCFM_Query->get_current_endpoint() && $this->is_seller() ) {
			$vendor_rules = array();
			foreach ( $rules as $rule ) {
				if ( isset( $rule['created_from'] ) && 'vendor_dashboard' === $rule['created_from'] ) {
					$vendor_rules[] = $rule;
				}
			}
			return $vendor_rules;
		} else {
			return $rules;
		}
	}


	public function conversation_page_content() {
		?>
			<div id="wholesalex_conversation_root_frontend"> </div>

		<?php
	}

	/**
	 * Conversation Lists and View Content
	 */
	public function conversations_content() {
		$action = 'listing';
		if ( isset( $_GET['conv'] ) && ! empty( $_GET['conv'] ) ) {
			$action = 'view';
		}
		switch ( $action ) {
			case 'listing':
				$this->conversation_page_content();
				break;
			case 'view':
				wp_enqueue_style( 'dashicons' );
				wp_enqueue_script( 'wholesalex-pro-public' );
				wp_localize_script(
					'wholesalex-pro-public',
					'wholesalex_conversation',
					array(
						'create_nonce'       => wp_create_nonce( 'wholesalex-new-conversation' ),
						'recaptcha_site_key' => wholesalex()->get_setting( '_settings_google_recaptcha_v3_site_key' ),
						'recaptcha_status'   => wholesalex()->get_setting( 'wsx_addon_recaptcha' ),
					)
				);

				do_action( 'wholesalex_conversation_metabox_content_account_page' );
				$wcfm_page = get_wcfm_page();

				AccountPage::view_conversation( sanitize_key( $_GET['conv'] ), wcfm_get_endpoint_url( 'wholesalex-conversations', '', $wcfm_page ) );
				break;

			default:
				// code...
				break;
		}
	}

	/**
	 * Modify Get Conversations Query on WholesaleX Conversatiosn Page to hide wcfm vendor messages
	 *
	 * @param array  $args WP_Query Args
	 * @param boolen $is_frontend  check is the request comes from frontend/vendor dashboard
	 * @return array
	 */
	public function modify_conversations_args( $args, $is_frontend ) {
		if ( $is_frontend ) {
			$q = array(
				'key'     => 'wholesalex_conversation_vid',
				'value'   => $this->get_vendor_id(),
				'compare' => '=',
			);
			if ( ! isset( $args['meta_query'] ) && is_array( $args['meta_query'] ) ) {
				$args['meta_query'] = array();
			}
			$args['meta_query'][] = $q;
		} else {
			$q = array(
				'key'     => 'wholesalex_conversation_vid',
				'compare' => 'NOT EXISTS',
			);
			if ( ! isset( $args['meta_query'] ) && is_array( $args['meta_query'] ) ) {
				$args['meta_query'] = array();
			}
			$args['meta_query'][] = $q;
		}

		return $args;
	}

		/**
		 * Add Vendors Fields in New Conversations
		 *
		 * @return void
		 */
	public function add_vendor_fields_in_conversation() {

		// if conversation enabled for vendor then

		$vendor_support = new WCFM_Vendor_Support();

		$all_vendors = $vendor_support->wcfm_get_vendor_list();
		?>
		<div class="wsx-conversation-element wsx-conversation-form-vendor-selection">
				<label for="text"><?php echo esc_html( wholesalex()->get_language_n_text( '_language_conversations_vendor', __( 'Vendor', 'wholesalex' ) ) ); ?></label>
				<select name="conversation_vendor" id="conversation_vendor">
				<?php
				foreach ( $all_vendors as $vid => $name ) {
					?>
						<option value="<?php echo esc_attr( $vid ); ?>"><?php echo esc_html( $name ); ?></option>
					<?php
				}
				?>
				</select>
			</div>

		<?php
	}


	/**
	 * Add Vendor as Valid Recipient
	 *
	 * @param int|string $conv_id Conversation ID
	 * @return void
	 */
	public function add_vendor_as_recipient( $conv_id ) {
		if ( isset( $_POST['wpnonce'] ) && wp_verify_nonce( sanitize_key( $_POST['wpnonce'] ), 'wholesalex-new-conversation' ) ) {

			if ( isset( $_POST['conversation_vendor'] ) && ! empty( $_POST['conversation_vendor'] ) ) { 
				$vendor_id = sanitize_text_field( $_POST['conversation_vendor'] ); 
				update_post_meta( $conv_id, 'wholesalex_conversation_vid', $vendor_id );
			}
		}

	}

	/**
	 * Add Vendor Columns on My Account Conversation Area
	 *
	 * @param array $columns Conversation Columns
	 * @return array
	 * @since 1.0.0
	 */
	public function add_vendor_columns( $columns ) {
		$columns = wholesalex()->insert_into_array( $columns, array( 'vendor' => wholesalex()->get_language_n_text( '_language_conversations_vendor', __( 'Vendor', 'wholesalex' ) ) ), 1 );
		return $columns;
	}

	/**
	 * Populate Vendor Column Data on Conversation Page (My account)
	 *
	 * @param string     $column_id Column Key.
	 * @param int|string $conv_id Conversation ID.
	 * @return void
	 */
	public function populate_vendor_column_data( $column_id, $conv_id ) {
		if ( 'vendor' == $column_id ) {
			$vendor_support = new WCFM_Vendor_Support();
			$vendor_id      = get_post_meta( $conv_id, 'wholesalex_conversation_vid', true );
			$store_name     = $vendor_support->wcfm_get_vendor_store_name_by_vendor( $vendor_id );
			?>
				<td class="wsx-conversation-list-item">
					<?php echo esc_html( $store_name ); ?>
				</td>
			<?php

		}
	}

	/**
	 * Allow Vendors to View Conversation
	 *
	 * @param string     $status Conversation Status.
	 * @param string|int $author_id Conversation Author ID.
	 * @param int|string $conv_id Conversation ID
	 * @return boolean
	 */
	public function allow_vendor_to_view_conversation( $status, $author_id, $conv_id ) {
		$recipient_vid = get_post_meta( $conv_id, 'wholesalex_conversation_vid', true );
		if ( $this->get_vendor_id() == $recipient_vid ) {
			$status = true;
		}

		return $status;

	}

	/**
	 * Add Vendor ID as Allowed Author
	 *
	 * @param array $allowed_authors Authors.
	 * @return array
	 */
	public function add_vendor_id_as_valid_post_author( $allowed_authors ) {
		if ( ! in_array( $this->get_vendor_id(), $allowed_authors ) ) {
			$allowed_authors[] = $this->get_vendor_id();
		}

		return $allowed_authors;
	}


	/**
	 * Add Conversation Vendor Reply class
	 *
	 * @param string $class Class Name.
	 * @param string $author Author ID.
	 * @param string $conv_id Conversation ID
	 * @return string
	 */
	public function add_conversation_vendor_reply_class( $class, $author, $conv_id ) {
		if ( $this->get_vendor_id() != $author ) {
			$class = 'wsx-reply-left';
		}

		return $class;
	}


	/**
	 * Add WholesaleX Pricing Fields on Vendors Product
	 *
	 * @param int|string $post_id Product ID.
	 * @return void
	 */
	public function add_wholesalex_pricing( $post_id ) {

		if ( ! $post_id ) {
			return;
		}
		wp_enqueue_script( 'wholesalex_product' );

		$discounts   = array();
		$is_variable = false;
		if ( $post_id ) {
			$product = wc_get_product( $post_id );
			if ( $product ) {
				$is_variable = 'variable' === $product->get_type();
				if ( $is_variable ) {
					if ( $product->has_child() ) {
						$childrens = $product->get_children();
						foreach ( $childrens as $key => $child_id ) {
							$discounts[ $child_id ] = wholesalex()->get_single_product_discount( $child_id );
						}
					}
				} else {
					$discounts[ $post_id ] = wholesalex()->get_single_product_discount( $post_id );
				}
			}
		}

		wp_localize_script(
			'wholesalex_components',
			'wholesalex_single_product',
			array(
				'fields'            => $this->get_product_fields(),
				'discounts'         => $discounts,
				'is_wcfm_dashboard' => true,
			),
		);

		if(!$is_variable) {
			?>
				<div class="_wholesalex_wcfm_single_product_settings wholesalex_simple_product options-group simple grouped hide_if_external hide_if_variable  wcfm_ele non-subscription non-variable-subscription non-auction non-redq_rental non-accommodation-booking non-lottery non-pw-gift-card wholesalex_wcfm"></div>
			<?php
		}
	}

	/**
	 * Add WholesaleX Pricing Fields on Vendors Product
	 *
	 * @param int|string $post_id Product ID.
	 * @return void
	 */
	public function add_variable_wholesalex_pricing( $post_id ) {

		if ( ! $post_id ) {
			return;
		}
		wp_enqueue_script( 'wholesalex_product' );

		$discounts   = array();
		$is_variable = false;
		if ( $post_id ) {
			$product = wc_get_product( $post_id );
			if ( $product ) {
				$is_variable = 'variable' === $product->get_type();
				if ( $is_variable ) {
					if ( $product->has_child() ) {
						$childrens = $product->get_children();
						foreach ( $childrens as $key => $child_id ) {
							$discounts[ $child_id ] = wholesalex()->get_single_product_discount( $child_id );
						}
					}
				} else {
					$discounts[ $post_id ] = wholesalex()->get_single_product_discount( $post_id );
				}
			}
		}

		wp_localize_script(
			'wholesalex_components',
			'wholesalex_single_product',
			array(
				'fields'            => $this->get_product_fields(),
				'discounts'         => $discounts,
				'is_wcfm_dashboard' => true,
			),
		);
		if($is_variable) {
			?>
				<div class="_wholesalex_wcfm_variable_product_settings options-group simple grouped hide_if_external hide_if_variable  wcfm_ele non-subscription non-variable-subscription non-auction non-redq_rental non-accommodation-booking non-lottery non-pw-gift-card wholesalex_wcfm"></div>
			<?php
		}
	}

	/**
	 * Single Product Field Return.
	 */
	public function get_product_fields() {
		$b2b_roles   = wholesalex()->get_roles( 'b2b_roles_option' );
		$b2c_roles   = wholesalex()->get_roles( 'b2c_roles_option' );
		$__b2b_roles = array();
		foreach ( $b2b_roles as $role ) {
			if ( ! ( isset( $role['value'] ) && isset( $role['value'] ) ) ) {
				continue;
			}
			$__b2b_roles[ $role['value'] ] = array(
				'label'    => $role['name'],
				'type'     => 'tiers',
				'is_pro'   => true,
				'pro_data' => array(
					'type'  => 'limit',
					'value' => 3,
				),
				'attr'     => array(
					'_prices'               => array(
						'type' => 'prices',
						'attr' => array(
							'wholesalex_base_price' => array(
								'type'    => 'number',
								'label'   => __( 'Base Price', 'wholesalex' ),
								'default' => '',
							),
							'wholesalex_sale_price' => array(
								'type'    => 'number',
								'label'   => __( 'Sale Price', 'wholesalex' ),
								'default' => '',
							),
						),
					),
					$role['value'] . 'tier' => array(
						'type'   => 'tier',
						'_tiers' => array(
							'columns'     => array(
								__( 'Discount Type', 'wholesalex' ),
								/* translators: %s: WholesaleX Role Name */
								sprintf( __( ' %s Price', 'wholesalex' ), $role['name'] ),
								__( 'Min Quantity', 'wholesalex' ),
							),
							'data'        => array(
								'_discount_type'   => array(
									'type'    => 'select',
									'options' => array(
										''            => __( 'Choose Discount Type...', 'wholesalex' ),
										'amount'      => __( 'Discount Amount', 'wholesalex' ),
										'percentage'  => __( 'Discount Percentage', 'wholesalex' ),
										'fixed_price' => __( 'Fixed Price', 'wholesalex' ),
									),
									'default' => '',
									'label'   => __( 'Discount Type', 'wholesalex' ),
								),
								'_discount_amount' => array(
									'type'        => 'number',
									'placeholder' => '',
									'default'     => '',
									'label'       => /* translators: %s: WholesaleX Role Name */
									sprintf( __( ' %s Price', 'wholesalex' ), $role['name'] ),
								),
								'_min_quantity'    => array(
									'type'        => 'number',
									'placeholder' => '',
									'default'     => '',
									'label'       => __( 'Min Quantity', 'wholesalex' ),
								),
							),
							'add'         => array(
								'type'  => 'button',
								'label' => __( 'Add Price Tier', 'wholesalex' ),
							),
							'upgrade_pro' => array(
								'type'  => 'button',
								'label' => __( 'Go For Unlimited Price Tiers', 'wholesalex' ),
							),
						),
					),
				),
			);
		}

		$__b2c_roles = array();
		foreach ( $b2c_roles as $role ) {
			if ( ! ( isset( $role['value'] ) && isset( $role['value'] ) ) ) {
				continue;
			}
			$__b2c_roles[ $role['value'] ] = array(
				'label'    => $role['name'],
				'type'     => 'tiers',
				'is_pro'   => true,
				'pro_data' => array(
					'type'  => 'limit',
					'value' => 2,
				),
				'attr'     => array(
					$role['value'] . 'tier' => array(
						'type'   => 'tier',
						'_tiers' => array(
							'columns'     => array(
								__( 'Discount Type', 'wholesalex' ),
								/* translators: %s: WholesaleX Role Name */
								sprintf( __( ' %s Price', 'wholesalex' ), $role['name'] ),
								__( 'Min Quantity', 'wholesalex' ),
							),
							'data'        => array(
								'_discount_type'   => array(
									'type'    => 'select',
									'options' => array(
										''            => __( 'Choose Discount Type...', 'wholesalex' ),
										'amount'      => __( 'Discount Amount', 'wholesalex' ),
										'percentage'  => __( 'Discount Percentage', 'wholesalex' ),
										'fixed_price' => __( 'Fixed Price', 'wholesalex' ),
									),
									'label'   => __( 'Discount Type', 'wholesalex' ),
									'default' => '',
								),
								'_discount_amount' => array(
									'type'        => 'number',
									'placeholder' => '',
									'label'       => /* translators: %s: WholesaleX Role Name */
									sprintf( __( ' %s Price', 'wholesalex' ), $role['name'] ),
									'default'     => '',
								),
								'_min_quantity'    => array(
									'type'        => 'number',
									'placeholder' => '',
									'default'     => '',
									'label'       => __( 'Min Quantity', 'wholesalex' ),
								),
							),
							'add'         => array(
								'type'  => 'button',
								'label' => __( 'Add Price Tier', 'wholesalex' ),
							),
							'upgrade_pro' => array(
								'type'  => 'button',
								'label' => __( 'Go For Unlimited Price Tiers', 'wholesalex' ),
							),
						),
					),
				),
			);
		}

		return apply_filters(
			'wholesalex_single_product_fields',
			array(
				'_b2c_section' => array(
					'label' => '',
					'attr'  => apply_filters( 'wholesalex_single_product_b2c_roles_tier_fields', $__b2c_roles ),
				),
				'_b2b_section' => array(
					'label' => __( 'WholesaleX B2B Special', 'wholesalex' ),
					'attr'  => apply_filters( 'wholesalex_single_product_b2b_roles_tier_fields', $__b2b_roles ),
				),
			),
		);
	}

	/**
	 * WholesaleX Section on WCFM Edit Product Page
	 * This section contain selection of tier layout and product visibility control
	 *
	 * @since 1.2.4
	 */
	public function wholesalex_section( $product_id ) {
		if ( ! $product_id ) {
			return;
		}

		// Enqueue wholesalex_product Script, which already registered in wholesalex free version
		wp_enqueue_script( 'wholesalex_product' );

		// Get product wholesalex settings
		$settings = wholesalex()->get_single_product_setting();

		// Localize WholesaleX Fields for this section and settings.
		wp_localize_script(
			'wholesalex_components',
			'wholesalex_product_tab',
			array(
				'fields'   => WHOLESALEX_Product::get_product_settings(),
				'settings' => isset( $settings[ $product_id ] ) ? $settings[ $product_id ] : array(),
			),
		);
		?>
			<div class="page_collapsible wholesalex_section  simple variable external grouped booking" id="wholesalex_section_wcfm">
				<label class="wcfmfa fa-server"></label><?php echo esc_html__( 'WholesaleX', 'wholesalex-wcfm-b2b-multivendor-marketplace' ); ?>
			</div>
			<div class="wcfm-container  simple variable external grouped booking">
				<div id="wholesalex_section_wcfm_expander" class="wcfm-content">
					<div class="wcfm_clearfix"></div><br />
						<div class="panel woocommerce_options_panel" id="wholesalex_tab_data"></div>
					<div class="wcfm_clearfix"></div><br />
				</div>
			</div>
			
		<?php
	}


	public function add_variations_data( $fields ) {
		$fields['wholesalex'] = array(
			'type'  => 'html',
			'value' => '',
			'class' => '_wholesalex_wcfm_variable_product_settings options-group wcfm_ele simple variable external grouped booking wholesalex_wcfm',
		);
		return $fields;
	}



	/**
	 * Save WholesaleX Single Product Data
	 * Save Tiered Pricing and Rolewise Pricing
	 *
	 * @param int|string $product_id Product ID.
	 * @param array      $product_data Product Data.
	 * @return void
	 */
	public function save_wholesalex_data( $product_id, $product_data ) {
		if ( isset( $product_data[ 'wholesalex_wcfm_simple_product_tiers_' . $product_id ] ) ) {
			$product_discounts = $this->sanitize( json_decode( wp_unslash( $product_data[ 'wholesalex_wcfm_simple_product_tiers_' . $product_id ] ), true ) );
			wholesalex()->save_single_product_discount( $product_id, $product_discounts );
		}

		if ( isset( $product_data['wholesalex_product_settings'] ) ) {
			$product_settings = wholesalex()->sanitize( json_decode( wp_unslash( $product_data['wholesalex_product_settings'] ), true ) );
			wholesalex()->save_single_product_settings( $product_id, $product_settings );
		}
	}


	/**
	 * Save Variation Data
	 *
	 * @param int|string $product_id Product ID.
	 * @param int|string $variation_id Variation ID.
	 * @param array $variations Variations Data.
	 * @param array $product_data Product Data
	 * @return void
	 */
	public function save_wholesalex_variation_data( $product_id, $variation_id, $variations, $product_data ) {
		if ( isset( $product_data[ 'wholesalex_wcfm_product_variation_tiers_' . $variation_id ] ) ) {
			$product_discounts = $this->sanitize( json_decode( wp_unslash( $product_data[ 'wholesalex_wcfm_product_variation_tiers_' . $variation_id ] ), true ) );
			wholesalex()->save_single_product_discount( $variation_id, $product_discounts );
		}
	}

	/**
	 * WholesaleX WCFM Sanitizer
	 *
	 * @param array $data Data.
	 * @since 1.0.0
	 * @return array $data Sanitized Array
	 */
	public function sanitize( $data ) {
		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				$data[ $key ] = $this->sanitize( $value );
			} else {
				$data[ $key ] = sanitize_text_field( $value );
			}
		}
		return $data;
	}

	/**
	 * Adding Checkbox in WCFM Multivendor Membership Plugin For Wholesalex Role Assign
	 *
	 * @param [array] $fields
	 * @since 1.0.1
	 * @return array
	 */
   public function wholesalex_add_custom_registration_checkbox( $fields ) {
		$wsx_plugin_name = apply_filters( 'wholesalex_plugin_name', __( 'WholesaleX', 'wholesalex' ) );
        if ( class_exists( 'WCFM' ) ) {
			$saved_value = get_option('wcfmvm_registration_static_fields', array());
			$fields['wholesalex_role'] = array(
				'label'       => __( 'Enable '.$wsx_plugin_name.' User Roles', 'wholesalex' ),
				'type'        => 'checkbox',
				'name'        => 'wcfmvm_registration_static_fields[wholesalex_role]',
				'class'       => 'wcfm-checkbox wcfm_ele',
				'label_class' => 'wcfm_title checkbox-title',
				'value'       => 'yes',
				'dfvalue'     => isset($saved_value['wholesalex_role']) && $saved_value['wholesalex_role'] === 'yes' ? 'yes' : ''
			);
			$fields['wholesalex_role_required'] = array(
				'label'       => __( 'Require \'User Roles\' Selection', 'wholesalex' ),
				'type'        => 'checkbox',
				'name'        => 'wcfmvm_registration_static_fields[wholesalex_role_required]',
				'class'       => 'wcfm-checkbox wcfm_ele',
				'label_class' => 'wcfm_title checkbox-title',
				'value'       => 'yes',
				'dfvalue'     => isset($saved_value['wholesalex_role_required']) && $saved_value['wholesalex_role_required'] === 'yes' ? 'yes' : ''
			);
			$fields['wholesalex_role_field_label'] = array(
				'label'       => __( $wsx_plugin_name . ' \'User Roles\' Label Text', 'wholesalex' ),
				'type'        => 'text',
				'name'        => 'wcfmvm_registration_static_fields[wholesalex_role_field_label]',
				'class'       => 'wcfm-text wcfm_ele multi_input_block_element',
				'label_class' => 'wcfm_title checkbox-title',
				'value'       => isset($saved_value['wholesalex_role_field_label']) ? $saved_value['wholesalex_role_field_label'] : '',
				'dfvalue'     => isset($saved_value['wholesalex_role_field_label']) ? $saved_value['wholesalex_role_field_label'] : 'WholesaleX Role'
			);
		}
        return $fields;
    }
	
	/**
	 * Adding Role Select Option in WCFM Multivendor Membership Plugin Front End For Wholesalex Role Assign
	 *
	 * @since 1.0.1
	 * @return void
	 */
	public function wholesalex_add_custom_select_markup() {
		$saved_value = get_option('wcfmvm_registration_static_fields', array());
		if ( isset($saved_value['wholesalex_role']) && $saved_value['wholesalex_role'] === 'yes' ) {
			if ( class_exists( 'WCFM' ) ) {
				if ( function_exists( 'wholesalex' ) && method_exists( wholesalex(), 'get_roles' ) ) {
					$roles_options = wholesalex()->get_roles( 'roles_option' );
					$transformed_array = array();
					?>
					<p class="wholesalex_role_8c9a7 wcfm_title">
						<strong>
							<?php echo isset($saved_value['wholesalex_role_field_label']) ? esc_html($saved_value['wholesalex_role_field_label']) : 'WholesaleX Role'; ?>
							<?php if (isset($saved_value['wholesalex_role_required']) && $saved_value['wholesalex_role_required'] === 'yes') : ?>
								<span class="required">*</span>
							<?php endif; ?>
						</strong>
					</p>
					<label class="screen-reader-text" for="wholesalex_role_8c9a7">
						wholesalex role 
						<?php if (isset($saved_value['wholesalex_role_required']) && $saved_value['wholesalex_role_required'] === 'yes') : ?>
							<span class="required">*</span>
						<?php endif; ?>
					</label>
					<select id="wholesalex_role_8c9a7" name="wcfmvm_custom_infos[wsx_wholesale_role]" class="wcfm-select" data-required="1" data-required_message="<?php echo isset($saved_value['wholesalex_role_field_label']) ? esc_html($saved_value['wholesalex_role_field_label']) : 'WholesaleX Role'; ?>: This field is required.">
						<option value="<?php echo isset($saved_value['wholesalex_role_required']) && $saved_value['wholesalex_role_required'] === 'yes' ? '' : 'wsx_select_role'; ?>">
							<?php esc_html_e( 'Select Role', 'text-domain' ); ?>
						</option>
						<?php
						foreach ($roles_options as $role) {
							$transformed_array[$role['value']] = $role['name'];
							?>
							<option value="<?php echo esc_attr($role['value']); ?>">
								<?php echo esc_html($role['name']); ?>
							</option>
							<?php
						}
						?>
					</select>
					<?php
				}

			}
		}
	}

	/**
	 * WholesaleX user Role Assign Option
	 *
	 * @param [int] $member_id
	 * @param [array] $user_data
	 * @since 1.0.1
	 * @return void
	 */
	public function wholesalex_wcfm_membership_registration_data( $member_id, $user_data) {
		if ( method_exists( '\WHOLESALEX\Functions', 'change_role') && isset( $user_data['wcfmvm_custom_infos']['wsx_wholesale_role'] ) && $user_data['wcfmvm_custom_infos']['wsx_wholesale_role'] != 'wsx_select_role' ) {
			update_user_meta( $member_id, '__wholesalex_registration_role', $user_data['wcfmvm_custom_infos']['wsx_wholesale_role'] );
			$__user_status_option = apply_filters( 'wholesalex_registration_form_user_status_option', 'admin_approve',$member_id, $user_data['wcfmvm_custom_infos']['wsx_wholesale_role'] );
			do_action( 'wholesalex_registration_form_user_status_' . $__user_status_option, $member_id, $user_data['wcfmvm_custom_infos']['wsx_wholesale_role'] );
			$__user_login_option = apply_filters( 'wholesalex_registration_form_user_login_option', 'manual_login' );
			do_action( 'wholesalex_registration_form_user_' . $__user_login_option, $member_id, $user_data['wcfmvm_custom_infos']['wsx_wholesale_role'] );

		}
	}

}
