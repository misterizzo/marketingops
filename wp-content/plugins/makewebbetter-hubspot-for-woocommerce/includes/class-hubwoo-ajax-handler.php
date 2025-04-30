<?php
/**
 * Handles all admin ajax requests.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    makewebbetter-hubspot-for-woocommerce
 * @subpackage makewebbetter-hubspot-for-woocommerce/includes
 */

if ( ! class_exists( 'HubWooAjaxHandler' ) ) {

	/**
	 * Handles all admin ajax requests.
	 *
	 * All the functions required for handling admin ajax requests
	 * required by the plugin.
	 *
	 * @package    makewebbetter-hubspot-for-woocommerce
	 * @subpackage makewebbetter-hubspot-for-woocommerce/includes
	 */
	class Hubwoo_Ajax_Handler {

		/**
		 * Class constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			// check oauth access token.
			add_action( 'wp_ajax_hubwoo_check_oauth_access_token', array( &$this, 'hubwoo_check_oauth_access_token' ) );
			// create group for properties.
			add_action( 'wp_ajax_hubwoo_create_property_group', array( &$this, 'hubwoo_create_property_group' ) );
			// get group properties.
			add_action( 'wp_ajax_hubwoo_get_group_properties', array( &$this, 'hubwoo_get_group_properties' ) );
			// create property.
			add_action( 'wp_ajax_hubwoo_create_group_property', array( &$this, 'hubwoo_create_group_property' ) );
			// create deal properties.
			add_action( 'wp_ajax_hubwoo_deals_create_property', array( &$this, 'hubwoo_deals_create_property' ) );
			// get final lists to be created.
			add_action( 'wp_ajax_hubwoo_get_lists', array( &$this, 'hubwoo_get_lists_to_create' ) );
			// create bulk lists.
			add_action( 'wp_ajax_hubwoo_create_list', array( &$this, 'hubwoo_create_list' ) );
			// create single single group on admin call..
			add_action( 'wp_ajax_hubwoo_create_single_group', array( &$this, 'hubwoo_create_single_group' ) );
			// create single property on admin call.
			add_action( 'wp_ajax_hubwoo_create_single_property', array( &$this, 'hubwoo_create_single_property' ) );
			// create single list on admin call.
			add_action( 'wp_ajax_hubwoo_create_single_list', array( &$this, 'hubwoo_create_single_list' ) );
			// create workflow.
			add_action( 'wp_ajax_hubwoo_create_single_workflow', array( &$this, 'hubwoo_create_single_workflow' ) );
			// updating workflow which are dependent.
			add_action( 'wp_ajax_hubwoo_update_workflow_tab', array( &$this, 'hubwoo_update_workflow_tab' ) );
			// search for order statuses.
			add_action( 'wp_ajax_hubwoo_search_for_order_status', array( &$this, 'hubwoo_search_for_order_status' ) );
			// get user roles for batch sync.
			add_action( 'wp_ajax_hubwoo_get_for_user_roles', array( &$this, 'hubwoo_get_for_user_roles' ) );
			// instant sync of users to HubSpot.
			add_action( 'wp_ajax_hubwoo_ocs_instant_sync', array( &$this, 'hubwoo_ocs_instant_sync' ) );
			// emailing the errors to makewebbetter support.
			add_action( 'wp_ajax_hubwoo_email_the_error_log', array( &$this, 'hubwoo_email_the_error_log' ) );
			// disconnect the current account and delete all of the meta.
			add_action( 'wp_ajax_hubwoo_disconnect_account', array( &$this, 'hubwoo_disconnect_account' ) );
			// get all of the users for the current selected user roles.
			add_action( 'wp_ajax_hubwoo_get_user_for_current_roles', array( &$this, 'hubwoo_get_user_for_current_roles' ) );
			// get sync status of any background sync process.
			add_action( 'wp_ajax_hubwoo_get_current_sync_status', array( &$this, 'hubwoo_get_current_sync_status' ) );
			// save objects in database option table.
			add_action( 'wp_ajax_hubwoo_save_updates', array( &$this, 'hubwoo_save_updates' ) );
			// get the all of the deal stages for select2.
			add_action( 'wp_ajax_hubwoo_deals_search_for_stages', array( &$this, 'hubwoo_deals_search_for_stages' ) );
			// run processes for the ecommerce pipeline setup.
			add_action( 'wp_ajax_hubwoo_ecomm_setup', array( &$this, 'hubwoo_ecomm_setup' ) );
			// get the current ocs count for deals.
			add_action( 'wp_ajax_hubwoo_ecomm_get_ocs_count', array( &$this, 'hubwoo_ecomm_get_ocs_count' ) );
			// manage sync processes.
			add_action( 'wp_ajax_hubwoo_manage_sync', array( &$this, 'hubwoo_manage_sync' ) );
			// manage historical objects vids.
			add_action( 'wp_ajax_hubwoo_manage_vids', array( &$this, 'hubwoo_manage_vids' ) );
			// track sync statuses of background executable tasks.
			add_action( 'wp_ajax_hubwoo_sync_status_tracker', array( &$this, 'hubwoo_sync_status_tracker' ) );
			// submit the onboarding question form .
			add_action( 'wp_ajax_hubwoo_onboard_form', array( &$this, 'hubwoo_onboard_form' ) );
			// get the onboarding data questionaire.
			add_action( 'wp_ajax_hubwoo_get_onboard_form', array( &$this, 'hubwoo_get_onboard_form' ) );

			// CSV files generation.
			add_action( 'wp_ajax_hubwoo_ocs_historical_contact', array( $this, 'hubwoo_ocs_historical_contact' ) );

			// Import csv to hubspot.
			add_action( 'wp_ajax_hubwoo_historical_contact_sync', array( $this, 'hubwoo_historical_contact_sync' ) );

			// Import historical products data.
			add_action( 'wp_ajax_hubwoo_historical_products_import', array( $this, 'hubwoo_historical_products_import' ) );

			// Import historical deals data.
			add_action( 'wp_ajax_hubwoo_historical_deals_sync', array( $this, 'hubwoo_historical_deals_sync' ) );
			// Hide review notice.
			add_action( 'wp_ajax_hubwoo_hide_rev_notice', array( $this, 'hubwoo_hide_rev_notice' ) );
			// Hide hpos notice.
			add_action( 'wp_ajax_hubwoo_hide_hpos_notice', array( $this, 'hubwoo_hide_hpos_notice' ) );
			// Get database data.
			add_action( 'wp_ajax_hubwoo_get_datatable_data', array( $this, 'hubwoo_get_datatable_data' ) );
			// Download database log.
			add_action( 'wp_ajax_hubwoo_download_sync_log', array( $this, 'hubwoo_download_sync_log' ) );
			// Clear database log.
			add_action( 'wp_ajax_hubwoo_clear_sync_log', array( $this, 'hubwoo_clear_sync_log' ) );
			// Fetch pipeline deal stages.
			add_action( 'wp_ajax_hubwoo_fetch_deal_stages', array( $this, 'hubwoo_fetch_deal_stages' ) );
			// Fetch deal pipelines.
			add_action( 'wp_ajax_hubwoo_fetch_update_pipelines', array( $this, 'hubwoo_fetch_update_pipelines' ) );
		}

		/**
		 * Checking access token validity.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_check_oauth_access_token() {

			$response = array(
				'status'  => true,
				'message' => esc_html__( 'Success', 'makewebbetter-hubspot-for-woocommerce' ),
			);

			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			if ( Hubwoo::is_access_token_expired() ) {

				$hapikey = HUBWOO_CLIENT_ID;
				$hseckey = HUBWOO_SECRET_ID;
				$status  = HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey );

				if ( ! $status ) {

					$response['status']  = false;
					$response['message'] = esc_html__( 'Something went wrong. Please verify your HubSpot Connection once.', 'makewebbetter-hubspot-for-woocommerce' );
				}
			}

			echo wp_json_encode( $response );

			wp_die();
		}

		/**
		 * Create new group for contact properties.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_create_property_group() {

			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );
			if ( ! empty( $_POST['groupName'] ) ) {
				$group_name = sanitize_key( wp_unslash( $_POST['groupName'] ) );
			}
			$object_type   = 'contacts';
			$groups        = HubWooContactProperties::get_instance()->_get( 'groups' );
			$group_details = array();
			if ( ! empty( $groups ) ) {
				foreach ( $groups as $single_group ) {
					if ( $single_group['name'] == $group_name ) {
						$group_details = $single_group;
						break;
					}
				}
			}
			$response = HubWooConnectionMananager::get_instance()->create_group( $group_details, $object_type );
			echo wp_json_encode( $response );
			wp_die();
		}

		/**
		 * Get hubwoo group properties by group name.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_get_group_properties() {

			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			if ( isset( $_POST['groupName'] ) ) {

				$group_name = sanitize_text_field( wp_unslash( $_POST['groupName'] ) );
				$properties = HubWooContactProperties::get_instance()->_get( 'properties', $group_name );
				echo wp_json_encode( $properties );
			}

			wp_die();
		}

		/**
		 * Create an group property on ajax request.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_create_group_property() {

			// check the nonce sercurity.
			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			if ( isset( $_POST['propertyDetails'] ) ) {
				$property_details = map_deep( wp_unslash( $_POST['propertyDetails'] ), 'sanitize_text_field' );
				$response         = HubWooConnectionMananager::get_instance()->create_batch_properties( $property_details, 'contact' );
				$response['body'] = json_decode( $response['body'], true );
				echo wp_json_encode( $response );
				wp_die();
			}
		}

		/**
		 * Create deal group property on ajax request.
		 *
		 * @since 1.4.0
		 */
		public function hubwoo_deals_create_property() {
			// check the nonce sercurity.
			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			$product_properties  = Hubwoo::hubwoo_get_product_properties();
			$object_type         = 'products';
			$response            = HubWooConnectionMananager::get_instance()->create_batch_properties( $product_properties, $object_type );
			if ( 201 == $response['status_code'] || 207 == $response['status_code'] ) {
				update_option( 'hubwoo_product_property_created', 'yes' );
				$response['body']    = json_decode( $response['body'], true );
			} else if ( 403 == $response['status_code'] ) {
				update_option( 'hubwoo_product_scope_needed', 'yes' );
				update_option( 'hubwoo_ecomm_setup_completed', 'yes' );
			}

			$deal_properties  = Hubwoo::hubwoo_get_deal_properties();
			$object_type      = 'deals';
			$response         = HubWooConnectionMananager::get_instance()->create_batch_properties( $deal_properties, $object_type );
			if ( 201 == $response['status_code'] || 207 == $response['status_code'] ) {
				update_option( 'hubwoo_deal_property_created', 'yes' );
				$response['body']    = json_decode( $response['body'], true );
			}

			echo wp_json_encode( $response );
			wp_die();
		}


		/**
		 * Get lists to be created on husbpot.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_get_lists_to_create() {

			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			$lists = HubWooContactProperties::get_instance()->_get( 'lists' );

			echo wp_json_encode( $lists );

			wp_die();
		}

		/**
		 * Create bulk lists on hubspot.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_create_list() {

			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			if ( isset( $_POST['listDetails'] ) ) {

				$list_details = map_deep( wp_unslash( $_POST['listDetails'] ), 'sanitize_text_field' );
				$response     = HubWooConnectionMananager::get_instance()->create_list( $list_details );
				echo wp_json_encode( $response );
			}

			wp_die();
		}


		/**
		 * Create single group on HubSpot.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_create_single_group() {

			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );
			if ( ! empty( $_POST['name'] ) ) {
				$group_name = sanitize_text_field( wp_unslash( $_POST['name'] ) );
			} else {
				$group_name = '';
			}
			$groups        = HubWooContactProperties::get_instance()->_get( 'groups' );
			$group_details = '';
			$object_type   = 'contacts';

			if ( is_array( $groups ) && count( $groups ) ) {

				foreach ( $groups as $single_group ) {

					if ( $single_group['name'] === $group_name ) {

						$group_details = $single_group;
						break;
					}
				}
			}

			if ( ! empty( $group_details ) ) {

				$response = HubWooConnectionMananager::get_instance()->create_group( $group_details, $object_type );
			}

			if ( isset( $response['status_code'] ) && ( 201 === $response['status_code'] || 409 === $response['status_code'] ) ) {

				$add_groups   = get_option( 'hubwoo-groups-created', array() );
				$add_groups[] = $group_details['name'];
				update_option( 'hubwoo-groups-created', $add_groups );
			}

			echo wp_json_encode( $response );
			wp_die();
		}

		/**
		 * Create single property on HubSpot.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_create_single_property() {

			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			if ( ! empty( $_POST['group'] ) ) {
				$group_name = sanitize_text_field( wp_unslash( $_POST['group'] ) );
			} else {
				$group_name = '';
			}

			if ( ! empty( $_POST['name'] ) ) {
				$property_name = sanitize_text_field( wp_unslash( $_POST['name'] ) );
			} else {
				$property_name = '';
			}

			$properties = HubWooContactProperties::get_instance()->_get( 'properties', $group_name );

			if ( ! empty( $properties ) && count( $properties ) ) {

				foreach ( $properties as $single_property ) {

					if ( ! empty( $single_property['name'] ) && $single_property['name'] == $property_name ) {

						$property_details = $single_property;
						break;
					}
				}
			}

			if ( ! empty( $property_details ) ) {

				$property_details['groupName'] = $group_name;

				$response = HubWooConnectionMananager::get_instance()->create_property( $property_details, 'contacts' );
			}

			if ( isset( $response['status_code'] ) && ( 201 === $response['status_code'] || 409 === $response['status_code'] ) ) {

				$add_properties   = get_option( 'hubwoo-properties-created', array() );
				$add_properties[] = $property_details['name'];
				update_option( 'hubwoo-properties-created', $add_properties );
			}

			echo wp_json_encode( $response );

			wp_die();
		}

		/**
		 * Create single list on hubspot.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_create_single_list() {

			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			if ( isset( $_POST['name'] ) ) {

				$list_name = sanitize_text_field( wp_unslash( $_POST['name'] ) );

				$lists = HubWooContactProperties::get_instance()->_get( 'lists' );

				if ( ! empty( $lists ) && count( $lists ) ) {

					foreach ( $lists as $single_list ) {

						if ( ! empty( $single_list['name'] ) && $single_list['name'] == $list_name ) {

							$list_details = $single_list;
							break;
						}
					}
				}

				if ( ! empty( $list_details ) ) {

					$response = HubWooConnectionMananager::get_instance()->create_list( $list_details );
				}

				if ( isset( $response['status_code'] ) && ( 200 === $response['status_code'] || 409 === $response['status_code'] ) ) {

					$add_lists   = get_option( 'hubwoo-lists-created', array() );
					$add_lists[] = $list_name;
					update_option( 'hubwoo-lists-created', $add_lists );
				}

				echo wp_json_encode( $response );
				wp_die();
			}
		}

		/**
		 * Create single list on hubspot.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_create_single_workflow() {

			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			if ( ! empty( $_POST['name'] ) ) {

				$name = sanitize_text_field( wp_unslash( $_POST['name'] ) );

				$add_workflows = get_option( 'hubwoo-workflows-created', array() );

				if ( in_array( $name, $add_workflows ) ) {
					return;
				}

				$workflows = HubWooContactProperties::get_instance()->_get( 'workflows' );

				if ( ! empty( $workflows ) ) {

					foreach ( $workflows as $single_workflow ) {

						if ( isset( $single_workflow['name'] ) && $single_workflow['name'] == $name ) {

							$workflow_details = $single_workflow;
							break;
						}
					}
				}

				if ( ! empty( $workflow_details ) ) {

					$response = HubWooConnectionMananager::get_instance()->create_workflow( $workflow_details );

					if ( isset( $response['status_code'] ) && ( 200 != $response['status_code'] ) ) {

						$response = HubwooErrorHandling::get_instance()->hubwoo_handle_response( $response, HubwooConst::HUBWOOWORKFLOW, array( 'current_workflow' => $workflow_details ) );
					}

					if ( 200 == $response['status_code'] ) {

						$add_workflows[] = $workflow_details['name'];
						update_option( 'hubwoo-workflows-created', $add_workflows );

						$workflow_data = isset( $response['body'] ) ? $response['body'] : '';

						if ( ! empty( $workflow_data ) ) {

							$workflow_data = json_decode(
								$workflow_data
							);
							$id            = isset( $workflow_data->id ) ? $workflow_data->id : '';
							update_option( $workflow_details['name'], $id );
						}
					}

					echo wp_json_encode( $response );
					wp_die();
				}
			}
		}

		/**
		 * Ajax search for order statuses.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_search_for_order_status() {

			$order_statuses = wc_get_order_statuses();

			$modified_order_statuses = array();

			if ( ! empty( $order_statuses ) ) {

				foreach ( $order_statuses as $status_key => $single_status ) {

					$modified_order_statuses[] = array( $status_key, $single_status );
				}
			}

			echo wp_json_encode( $modified_order_statuses );

			wp_die();
		}

		/**
		 * User roles for batch sync.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_get_for_user_roles() {

			global $hubwoo;

			$user_roles = $hubwoo->hubwoo_get_user_roles();

			$modified_order_statuses = array();

			if ( ! empty( $user_roles ) ) {

				foreach ( $user_roles as $user_key => $single_role ) {

					$modified_order_statuses[] = array( $user_key, $single_role );
				}
			}

			echo wp_json_encode( $modified_order_statuses );

			wp_die();
		}

		/**
		 * Callback to sync contacts to hubspot in 1 click.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_ocs_instant_sync() {

			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			if ( ! empty( $_POST['step'] ) ) {
				$step = sanitize_text_field( wp_unslash( $_POST['step'] ) );
			} else {
				$step = '';
			}

			$contact_sync    = true;
			$hubwoo_datasync = new HubwooDataSync();

			$total_need_syncing = $hubwoo_datasync->hubwoo_get_all_unique_user( true );

			if ( ! $total_need_syncing ) {

				$percentage_done = 100;
				$response        = array(
					'step'       => $step + 1,
					'progress'   => $percentage_done,
					'completed'  => true,
					'nocontacts' => true,
				);

				echo wp_json_encode( $response );
				wp_die();
			}

			$users_need_syncing = $hubwoo_datasync->hubwoo_get_all_unique_user();

			$user_data = array();

			$args = array();

			if ( is_array( $users_need_syncing ) && count( $users_need_syncing ) ) {

				$user_data    = $hubwoo_datasync->get_sync_data( $users_need_syncing );
				$args['ids']  = $users_need_syncing;
				$args['type'] = 'user';
			} else {

				$roles = get_option( 'hubwoo_customers_role_settings', array() );

				if ( in_array( 'guest_user', $roles, true ) ) {

					$contact_sync = false;

					$user_to_sync = $hubwoo_datasync->hubwoo_get_all_unique_user( false, 'guestOrder' );

					$user_data    = $hubwoo_datasync->get_guest_sync_data( $user_to_sync );
					$args['ids']  = $user_to_sync;
					$args['type'] = 'order';
				}
			}

			if ( ! empty( $user_data ) ) {

				if ( Hubwoo::is_valid_client_ids_stored() ) {

					$flag = true;

					if ( Hubwoo::is_access_token_expired() ) {

						$hapikey = HUBWOO_CLIENT_ID;
						$hseckey = HUBWOO_SECRET_ID;
						$status  = HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey );

						if ( ! $status ) {

							$flag = false;
						}
					}

					if ( $flag ) {
						$response = HubWooConnectionMananager::get_instance()->create_or_update_contacts( $user_data, $args );

						if ( ( count( $user_data ) > 1 ) && isset( $response['status_code'] ) && 400 === $response['status_code'] ) {

							if ( isset( $response['response'] ) ) {
								$error_response = json_decode( $response['response'], true );

								if ( isset( $error_response['failureMessages'] ) ) {

									$failure_messages = $error_response['failureMessages'];

									if ( is_array( $failure_messages ) ) {
										foreach ( $failure_messages as $failure_error ) {
											$property_validation_error = isset( $failure_error['propertyValidationResult'] ) ? 1 : 0;
											if ( $property_validation_error ) {
												$percentage_done = 100;
												$response        = array(
													'step' => $step + 1,
													'progress' => $percentage_done,
													'completed' => true,
													'propertyError' => true,
												);

												echo wp_json_encode( $response );
												wp_die();
											}
										}
									}
								}
							}
							$response = Hubwoo_Admin::hubwoo_split_contact_batch( $user_data );
						}
						$hsocssynced = get_option( 'hubwoo_ocs_contacts_synced', 0 );

						if ( ! empty( $user_to_sync ) ) {
							$hsocssynced += count( $user_to_sync );
						}

						if ( $contact_sync ) {
							foreach ( $users_need_syncing as $user_id ) {
								update_user_meta( $user_id, 'hubwoo_pro_user_data_change', 'synced' );
							}
						} else {
							foreach ( $user_to_sync as $order_id ) {
								//hpos changes
								$order = wc_get_order($order_id);
								$order->update_meta_data('hubwoo_pro_guest_order', 'synced');
								$order->save();
							}
						}

						update_option( 'hubwoo_ocs_contacts_synced', $hsocssynced );
					}
				}
			}

			$percentage_done = 0;
			$total_users     = get_option( 'hubwoo_total_ocs_need_sync', 0 );
			if ( $total_users ) {

				$synced          = $total_users - $total_need_syncing;
				$percentage      = ( $synced / $total_users ) * 100;
				$percentage_done = sprintf( '%.2f', $percentage );
			}

			$response = array(
				'step'             => $step + 1,
				'progress'         => $percentage_done,
				'totalNeedSyncing' => $total_need_syncing,
				'synced'           => wp_json_encode( $users_need_syncing ),
			);

			$contactqueue = $hubwoo_datasync->hubwoo_get_all_unique_user( true );

			if ( ! $contactqueue ) {
				$response['progress']  = 100;
				$response['completed'] = true;
				delete_option( 'hubwoo_total_ocs_need_sync' );
				delete_option( 'hubwoo_ocs_contacts_synced' );
			}

			echo wp_json_encode( $response );

			wp_die();
		}


		/**
		 * Update workflow listing window when a workflow is created.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_update_workflow_tab() {

			global $hubwoo;

			$created_workflows = get_option( 'hubwoo-workflows-created', '' );

			$workflows_dependencies = $hubwoo->hubwoo_workflows_dependency();

			$updated_tabs = array();

			$dependencies_count = 0;

			if ( is_array( $workflows_dependencies ) && count( $workflows_dependencies ) ) {
				foreach ( $workflows_dependencies as $workflows ) {
					$dependencies_count = count( $workflows['dependencies'] );
					$counter            = 0;
					foreach ( $workflows['dependencies'] as $dependencies ) {
						if ( is_array( $created_workflows ) && count( $created_workflows ) ) {
							if ( in_array( $dependencies, $created_workflows, true ) ) {
								$counter++;
							}
						}
					}
					if ( $counter === $dependencies_count ) {
						$updated_tabs[] = $workflows['workflow'];
					}
				}
			}
			echo wp_json_encode( $updated_tabs );
			wp_die();
		}

		/**
		 * Email the hubspot API error log.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_email_the_error_log() {

			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );
			$log_dir     = WC_LOG_DIR . 'hubspot-for-woocommerce-logs.log';
			$attachments = array( $log_dir );
			$to          = 'integrations@makewebbetter.com';
			$subject     = 'HubSpot Pro Error Logs';
			$headers     = array( 'Content-Type: text/html; charset=UTF-8' );
			$message     = 'admin email: ' . get_option( 'admin_email', '' ) . '<br/>';
			$status      = wp_mail( $to, $subject, $message, $headers, $attachments );

			if ( 1 === $status ) {
				$status = 'success';
			} else {
				$status = 'failure';
			}
			update_option( 'hubwoo_pro_alert_param_set', false );
			echo wp_json_encode( $status );
			wp_die();
		}


		/**
		 * Disconnect hubspot account.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_disconnect_account() {

			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			global $hubwoo;

			$delete_meta = false;

			if ( isset( $_POST['data'] ) ) {
				$data        = map_deep( wp_unslash( $_POST['data'] ), 'sanitize_text_field' );
				$delete_meta = 'yes' == $data['delete_meta'] ? true : false;
			}

			$hubwoo->hubwoo_switch_account( true, $delete_meta );
			echo wp_json_encode( true );
			wp_die();
		}

		/**
		 * Get wordpress/woocommerce user roles.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_get_user_for_current_roles() {
			// check the nonce sercurity.
			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			$hubwoo_data_sync = new HubwooDataSync();
			$unique_users     = $hubwoo_data_sync->hubwoo_get_all_unique_user( true );
			update_option( 'hubwoo_total_ocs_need_sync', $unique_users );
			echo wp_json_encode( $unique_users );
			wp_die();
		}

		/**
		 * Get sync status for contact/deal.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_get_current_sync_status() {
			// check the nonce sercurity.
			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );
			if ( ! empty( $_POST['data'] ) ) {
				$type = map_deep( wp_unslash( $_POST['data'] ), 'sanitize_text_field' );
			} else {
				$type = '';
			}
			if ( isset( $type['type'] ) ) {
				switch ( $type['type'] ) {
					case 'contact':
						$status = get_option( 'hubwoo_background_process_running', false );
						break;
					case 'deal':
						$status = get_option( 'hubwoo_deals_sync_running', 0 );
						break;
					default:
						$status = false;
						break;
				}
				echo wp_json_encode( $status );
			}
			wp_die();
		}

		/**
		 * Saving Updates to the Database.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_save_updates() {

			// check the nonce sercurity.
			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			if ( isset( $_POST['updates'] ) && ! empty( $_POST['action'] ) && current_user_can('manage_options') ) {
				
				$updates = map_deep( wp_unslash( $_POST['updates'] ), 'sanitize_text_field' );

				if ( isset( $_POST['type'] ) ) {
					$action = map_deep( wp_unslash( $_POST['type'] ), 'sanitize_text_field' );
					$status = false;
					if ( count( $updates ) ) {

						foreach ( $updates as $db_key => $value ) {

							if ( 'update' === $action ) {
								$value = 'EMPTY_ARRAY' === $value ? array() : $value;
								update_option( $db_key, $value );
							} elseif ( 'delete' === $action ) {
								delete_option( $value );
							}
						}

						$status = true;
					}
					echo wp_json_encode( $status );
					wp_die();
				}
			}
		}

		/**
		 * Saving Updates to the Database.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_manage_sync() {

			// check the nonce sercurity.
			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			if ( ! empty( $_POST['process'] ) ) {
				$process = map_deep( wp_unslash( $_POST['process'] ), 'sanitize_text_field' );

				if ( ! empty( $process ) ) {

					if ( 'start-deal' === $process ) {
						$orders_needs_syncing = Hubwoo_Admin::hubwoo_orders_count_for_deal();
						if ( $orders_needs_syncing ) {

							update_option( 'hubwoo_deals_sync_running', 1 );
							update_option( 'hubwoo_deals_sync_total', $orders_needs_syncing );
							as_schedule_recurring_action( time(), 300, 'hubwoo_deals_sync_background' );
						}
					} else {
						Hubwoo::hubwoo_stop_sync( $process );
					}
					echo true;
				}
			}

			wp_die();
		}

		/**
		 * Manages Vids to Database.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_manage_vids() {

			// check the nonce sercurity.
			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			$status = false;

			if ( ! empty( $_POST['process'] ) ) {
				$process = map_deep( wp_unslash( $_POST['process'] ), 'sanitize_text_field' );
				if ( ! empty( $process ) ) {
					switch ( $process ) {
						case 'contact':
							update_option( 'hubwoo_contact_vid_update', 1 );
							as_schedule_recurring_action( time(), 300, 'hubwoo_update_contacts_vid' );
							$status = true;
							break;
						case 'deal':
							delete_option( 'hubwoo_ecomm_order_date_allow' );
							$orders_needs_syncing = Hubwoo_Admin::hubwoo_orders_count_for_deal();
							if ( $orders_needs_syncing ) {
								update_option( 'hubwoo_deals_sync_running', 1 );
								update_option( 'hubwoo_deals_sync_total', $orders_needs_syncing );
								as_schedule_recurring_action( time(), 300, 'hubwoo_deals_sync_background' );
								$status = true;
							}
							break;
						default:
							break;
					}
				}
			}
			echo wp_json_encode( $status );
			wp_die();
		}

		/**
		 * Ajax call to search for deal stages.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_deals_search_for_stages() {

			$stages = get_option( 'hubwoo_fetched_deal_stages', array() );

			$existing_stages = array();

			$deal_stage_id = 'stageId';

			if ( 'yes' == get_option( 'hubwoo_ecomm_pipeline_created', 'no' ) ) {
				$deal_stage_id = 'id';
			}

			if ( is_array( $stages ) && count( $stages ) ) {

				foreach ( $stages as $stage ) {

					$existing_stages[] = array( $stage[ $deal_stage_id ], $stage['label'] );
				}
			}

			echo wp_json_encode( $existing_stages );
			wp_die();
		}

		/**
		 * Get orders count for 1 click sync.
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_ecomm_get_ocs_count() {

			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			$ocs_order_count = Hubwoo_Admin::hubwoo_orders_count_for_deal();
			if ( 1 != get_option( 'hubwoo_deals_sync_running', 0 ) ) {
				update_option( 'hubwoo_deals_current_sync_total', $ocs_order_count );
			}
			echo wp_json_encode( $ocs_order_count );
			wp_die();
		}

		/**
		 * Track sync percentage and eta for background processes
		 *
		 * @since 1.0.0
		 */
		public function hubwoo_sync_status_tracker() {

			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );
			$response = array(
				'percentage' => 0,
				'is_running' => 'no',
			);

			if ( isset( $_POST['process'] ) ) {
				$process = map_deep( wp_unslash( $_POST['process'] ), 'sanitize_text_field' );
				if ( ! empty( $process ) ) {
					switch ( $process ) {
						case 'contact':
							if ( get_option( 'hubwoo_background_process_running', false ) ) {

								$unique_users = Hubwoo::hubwoo_get_total_contact_need_sync();
								update_option( 'hubwoo_total_ocs_contact_need_sync', $unique_users );

								$users_to_sync          = get_option( 'hubwoo_total_ocs_contact_need_sync', 0 );
								$current_user_sync      = get_option( 'hubwoo_ocs_contacts_synced', 0 );
								$perc                   = round( $current_user_sync * 100 / $users_to_sync );
								$response['percentage'] = $perc > 100 ? 100 : $perc;
								$response['is_running'] = 'yes';

								if ( 100 == $response['percentage'] ) {
									update_option( 'hubwoo_background_process_running', false );
								}
							}

							break;
						case 'product':
							if ( 'yes' == get_option( 'hubwoo_start_product_sync', 'no' ) ) {
								$total_products = get_option( 'hubwoo_products_to_sync', 0 );
								$sync_result    = Hubwoo::hubwoo_make_db_query( 'total_synced_products' );
								if ( ! empty( $sync_result ) ) {
									$sync_result     = (array) $sync_result[0];
									$synced_products = $sync_result['COUNT(post_id)'];
									if ( 0 != $total_products ) {
										$response['percentage'] = round( $synced_products * 100 / $total_products );
										$response['eta']        = Hubwoo::hubwoo_create_sync_eta( $synced_products, $total_products, 3, 5 );
										$response['is_running'] = 'yes';
									}
								}
							}
							break;
						case 'order':
							if ( 1 == get_option( 'hubwoo_deals_sync_running', 0 ) ) {
								$data                   = Hubwoo::get_sync_status();
								$response['percentage'] = $data['deals_progress'];
								$response['eta']        = $data['eta_deals_sync'];
								$response['is_running'] = 'yes';
							}
							break;
					}
					echo wp_json_encode( $response );
				}
			}
			wp_die();
		}
		/**
		 * Upserting ecomm bridge settings for hubspot objects-contact,deal,product,line-item
		 *
		 * @since    1.0.0
		 */
		public function hubwoo_ecomm_setup() {

			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			$response = array(
				'status_code' => 404,
				'response'    => 'E-Commerce Bridge Setup Failed.',
			);

			if ( ! empty( $_POST['process'] ) ) {
				$process = map_deep( wp_unslash( $_POST['process'] ), 'sanitize_text_field' );

				if ( ! empty( $process ) ) {
					switch ( $process ) {
						case 'get-total-products':
							$store = Hubwoo::get_store_data();
							break;
						case 'update-deal-stages':
							$deal_stages           = Hubwoo::fetch_deal_stages_from_pipeline( 'Ecommerce Pipeline', false );
							$deal_model            = Hubwoo::hubwoo_deal_stage_model();
							$process_deal_stages   = array_map(
								function ( $deal_stage_data ) use ( $deal_model ) {
									$updates = ( isset( $deal_model[ $deal_stage_data['id'] ] ) && ! empty( $deal_model[ $deal_stage_data['id'] ] ) ) ? $deal_model[ $deal_stage_data['id'] ] : '';
									if ( ! empty( $updates ) ) {
										foreach ( $updates as $key => $value ) {
											if ( array_key_exists( $key, $deal_stage_data ) ) {
												$deal_stage_data[ $key ] = $value;
											}
										}
									}
									return $deal_stage_data;
								},
								$deal_stages['stages']
							);
							$deal_stages['stages'] = $process_deal_stages;
							$pipeline_id = $deal_stages['id'];
							unset( $deal_stages['id'] );
							$response              = HubWooConnectionMananager::get_instance()->update_deal_pipeline( $deal_stages, $pipeline_id );
							update_option( 'hubwoo_ecomm_final_mapping', Hubwoo::hubwoo_deals_mapping() );
							break;
						case 'reset-mapping':
							if ( ! empty( $_POST['pipeline'] ) ) {
								$selected_pipeline = map_deep( wp_unslash( $_POST['pipeline'] ), 'sanitize_text_field' );
								if ( 'Ecommerce Pipeline' == $selected_pipeline ) {
									update_option( 'hubwoo_ecomm_final_mapping', Hubwoo::hubwoo_deals_mapping() );
								} else if ( 'Sales Pipeline' == $selected_pipeline ) {
									update_option( 'hubwoo_ecomm_final_mapping', Hubwoo::hubwoo_sales_deals_mapping() );
								}
							} else {
								update_option( 'hubwoo_ecomm_final_mapping', '' );
							}
							break;
						case 'start-products-sync':
							if ( ! as_next_scheduled_action( 'hubwoo_products_sync_background' ) ) {
								update_option( 'hubwoo_start_product_sync', 'yes' );
								as_schedule_recurring_action( time(), 180, 'hubwoo_products_sync_background' );
							}
							$response['status_code'] = 200;
							$response['response']    = 'Product Sync-Status has been initiated';
							break;

					}
					echo wp_json_encode( $response );
				}
			}
			wp_die();
		}

		/**
		 * Get the onboarding submission data.
		 *
		 * @since    1.0.4
		 */
		public function hubwoo_get_onboard_form() {

			// check the nonce sercurity.
			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );
			if ( ! empty( $_POST['key'] ) ) {
				$key     = map_deep( wp_unslash( $_POST['key'] ), 'sanitize_text_field' );
				$key     = str_replace( '[]', '', $key );
				$options = array_map(
					function( $option ) {
						return array( $option, $option );
					},
					Hubwoo::hubwoo_onboarding_questionaire()[ $key ]['options']
				);
				echo json_encode( $options );
			}
			wp_die();
		}

		/**
		 * Handle the onboarding form submision.
		 *
		 * @since    1.0.4
		 */
		public function hubwoo_onboard_form() {
			// check the nonce sercurity.
			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			if ( ! empty( $_POST['formData'] ) ) {
				$form_data = map_deep( wp_unslash( $_POST['formData'] ), 'sanitize_text_field' );
				if ( ! empty( $form_data ) ) {
					$form_details = array();
					array_walk(
						$form_data,
						function( $field, $name ) use ( &$form_details ) {
							if ( is_array( $field ) ) {
								$field = HubwooGuestOrdersManager::hubwoo_format_array( $field );
							}
							$form_details['fields'][] = array(
								'name'  => $name,
								'value' => $field,
							);
						}
					);
					echo json_encode( HubWooConnectionMananager::get_instance()->submit_form_data( $form_details, '5373140', '0354594f-26ce-414d-adab-4e89f2104902' ) );
				}
			}
			wp_die();
		}


		/**
		 * Generate Contacts CSV file.
		 */
		public function hubwoo_ocs_historical_contact() {

			// Nonce verification.
			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			if ( ! empty( $_POST['step'] ) ) {
				$step = sanitize_text_field( wp_unslash( $_POST['step'] ) );
			} else {
				$step = '';
			}

			$hubwoo_datasync    = new HubwooDataSync();
			$total_need_syncing = $hubwoo_datasync->hubwoo_get_all_unique_user( true );
			$server_time = ini_get( 'max_execution_time' );

			if ( isset( $server_time ) && $server_time < 1500 ) {
				$server_time = 1500;
			}

			if ( ! $total_need_syncing ) {

				$percentage_done = 100;

				echo wp_json_encode(
					array(
						'step'     => $step + 1,
						'progress' => $percentage_done,
						'max_time' => empty( $server_time ) ? 1500 : $server_time,
						'status'   => true,
						'response' => true,
					)
				);
				wp_die();

			} else {
				$percentage_done = 0;

				echo wp_json_encode(
					array(
						'step'     => $step,
						'progress' => $percentage_done,
						'max_time' => empty( $server_time ) ? '1500' : $server_time,
						'status'   => true,
						'contact'  => $total_need_syncing,
						'response' => 'Historical contact data found.',
					)
				);
				wp_die();
			}

		}

		/**
		 * Import historical contacts csv to hubspot.
		 */
		public function hubwoo_historical_contact_sync() {

			// Nonce verification.
			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			if ( ! empty( $_POST['step'] ) ) {
				$step = sanitize_text_field( wp_unslash( $_POST['step'] ) );
			} else {
				$step = '';
			}

			if ( ! empty( $_POST['max_item'] ) ) {
				$max_item = sanitize_text_field( wp_unslash( $_POST['max_item'] ) );
			} else {
				$max_item = 15;
			}

			$con_get_vid = ! empty( $_POST['con_get_vid'] ) ? sanitize_text_field( wp_unslash( $_POST['con_get_vid'] ) ) : 'final_request';
			$skip_product = 'false';
			$user_ids = array();

			$contraints = array(
				array(
					'key'     => 'hubwoo_ecomm_pro_id',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'hubwoo_ecomm_invalid_pro',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'hubwoo_product_synced',
					'compare' => 'NOT EXISTS',
				),
				'relation' => 'AND',
			);

			$total_products = Hubwoo::hubwoo_ecomm_get_products( -1, $contraints );

			if ( 'yes' == get_option( 'hubwoo_product_scope_needed', 'no' ) ) {
				$total_products = array();
				$skip_product = 'true';
			}

			if ( count( $total_products ) == 0 ) {

				if( Hubwoo::hubwoo_check_hpos_active() ) {
					$query = new WC_Order_Query(array(
						'posts_per_page'      => -1,
						'post_status'         => array_keys( wc_get_order_statuses() ),
						'orderby'             => 'date',
						'order'               => 'desc',
						'return'              => 'ids',
						'no_found_rows'       => true,
						'ignore_sticky_posts' => true,
						'post_parent'         => 0,
						'meta_key'			  => 'hubwoo_ecomm_deal_created',
						'meta_compare'		  => 'NOT EXISTS',
					));

					$order_ids = $query->get_orders();
				} else {
					$query = new WP_Query();

					$order_args = array(
						'post_type'           => 'shop_order',
						'posts_per_page'      => -1,
						'post_status'         => array_keys( wc_get_order_statuses() ),
						'orderby'             => 'date',
						'order'               => 'desc',
						'fields'              => 'ids',
						'no_found_rows'       => true,
						'ignore_sticky_posts' => true,
						'meta_query'          => array(
							array(
								'key'     => 'hubwoo_ecomm_deal_created',
								'compare' => 'NOT EXISTS',
							),
						),
					);

					$order_ids = $query->query( $order_args );
				}
			}

			$hubwoo_datasync    = new HubwooDataSync();
			$user_ids           = $hubwoo_datasync->hubwoo_get_all_unique_user( false, 'customer', $max_item );

			$response           = '';

			if ( empty( $user_ids ) ) {
				$percentage_done = 100;
				echo wp_json_encode(
					array(
						'step'         => $step + 1,
						'progress'     => $percentage_done,
						'status'       => true,
						'total_prod'   => empty( $total_products ) ? 0 : count( $total_products ),
						'total_deals'  => empty( $order_ids ) ? 0 : count( $order_ids ),
						'skip_product' => $skip_product,
						'response'     => 'No Contact found.',
					)
				);
				wp_die();
			}

			foreach ( $user_ids as $user_id ) {

				$user_info             = json_decode( json_encode( get_userdata( $user_id ) ), true );
				$user_email            = $user_info['data']['user_email'];
				$contact               = array();
				$properties            = array();

				$hubwoo_customer = new HubWooCustomer( $user_id );
				$properties      = $hubwoo_customer->get_contact_properties();
				$user_properties = $hubwoo_customer->get_user_data_properties( $properties );
				foreach ( $user_properties as $key => $property ) {
					$contact[ $property['property'] ] = $property['value'];
				}
				$contact['email'] = $user_email;
				$contact = array(
					'properties' => $contact,
				);

				if ( ! empty( $contact ) ) {
					$flag = true;
					if ( Hubwoo::is_access_token_expired() ) {

						$hapikey = HUBWOO_CLIENT_ID;
						$hseckey = HUBWOO_SECRET_ID;
						$status  = HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey );

						if ( ! $status ) {

							$flag = false;
						}
					}

					if ( $flag ) {

						$response = HubWooConnectionMananager::get_instance()->create_object_record( 'contacts', $contact );

						if ( 201 == $response['status_code'] ) {
							$contact_vid = json_decode( $response['body'] );
							update_user_meta( $user_id, 'hubwoo_user_vid', $contact_vid->id );
							update_user_meta( $user_id, 'hubwoo_pro_user_data_change', 'synced' );

						} else if ( 409 == $response['status_code'] ) {
							$contact_vid = json_decode( $response['body'] );
							$hs_id = explode( 'ID: ', $contact_vid->message );
							$response = HubWooConnectionMananager::get_instance()->update_object_record( 'contacts', $hs_id[1], $contact );
							update_user_meta( $user_id, 'hubwoo_user_vid', $hs_id[1] );
							update_user_meta( $user_id, 'hubwoo_pro_user_data_change', 'synced' );
						} else if ( 400 == $response['status_code'] ) {
							update_user_meta( $user_id, 'hubwoo_invalid_contact', 'yes' );
							update_user_meta( $user_id, 'hubwoo_pro_user_data_change', 'synced' );
						}

						do_action( 'hubwoo_ecomm_contact_synced', $user_email );
					}
				}
			}

			echo wp_json_encode(
				array(
					'step'         => $step + 1,
					'status'       => true,
					'total_prod'   => empty( $total_products ) ? 0 : count( $total_products ),
					'total_deals'  => empty( $order_ids ) ? 0 : count( $order_ids ),
					'skip_product' => $skip_product,
					'response'     => 'Historical contacts synced successfully.',
				)
			);
			wp_die();

		}

		/**
		 * Sync historical products data.
		 */
		public function hubwoo_historical_products_import() {

			// Nonce verification.
			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			$order_ids = array();

			$step = ! empty( $_POST['step'] ) ? sanitize_text_field( wp_unslash( $_POST['step'] ) ) : '';

			$pro_get_vid = ! empty( $_POST['pro_get_vid'] ) ? sanitize_text_field( wp_unslash( $_POST['pro_get_vid'] ) ) : 'final_request';

			if ( ! empty( $_POST['max_item'] ) ) {
				$max_item = sanitize_text_field( wp_unslash( $_POST['max_item'] ) );
			} else {
				$max_item = 15;
			}

			if( Hubwoo::hubwoo_check_hpos_active() ) {
				$query = new WC_Order_Query(array(
					'posts_per_page'      => -1,
					'post_status'         => array_keys( wc_get_order_statuses() ),
					'orderby'             => 'date',
					'order'               => 'desc',
					'return'              => 'ids',
					'no_found_rows'       => true,
					'ignore_sticky_posts' => true,
					'post_parent'         => 0,
					'meta_key'			  => 'hubwoo_ecomm_deal_created',
					'meta_compare'		  => 'NOT EXISTS',
				));

				$order_ids = $query->get_orders();
			} else {

				$query = new WP_Query();

				$order_args = array(
					'post_type'           => 'shop_order',
					'posts_per_page'      => -1,
					'post_status'         => array_keys( wc_get_order_statuses() ),
					'orderby'             => 'date',
					'order'               => 'desc',
					'fields'              => 'ids',
					'no_found_rows'       => true,
					'ignore_sticky_posts' => true,
					'meta_query'          => array(
						array(
							'key'     => 'hubwoo_ecomm_deal_created',
							'compare' => 'NOT EXISTS',
						),
					),
				);

				$order_ids = $query->query( $order_args );
			}

			$product_data = Hubwoo::hubwoo_get_product_data( $max_item );

			if ( empty( $product_data ) ) {
				echo wp_json_encode(
					array(
						'step'     => $step + 1,
						'status'   => true,
						'total_deals' => empty( $order_ids ) ? '' : count( $order_ids ),
						'response' => 'No products found to sync.',
					)
				);
				wp_die();
			}

			if ( ! empty( $product_data ) && is_array( $product_data ) ) {
				foreach ( $product_data as $pro_id => $value ) {

					$filtergps = array();
					$product_data = array(
						'properties' => $value['properties'],
					);

					$flag = true;
					if ( Hubwoo::is_access_token_expired() ) {

						$hapikey = HUBWOO_CLIENT_ID;
						$hseckey = HUBWOO_SECRET_ID;
						$status  = HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey );

						if ( ! $status ) {

							$flag = false;
						}
					}

					if ( $flag ) {

						$product = wc_get_product( $pro_id );

						$filtergps = array(
							'filterGroups' => array(
								array(
									'filters' => array(
										array(
											'value' => $pro_id,
											'propertyName' => 'store_product_id',
											'operator' => 'EQ',
										),
									),
								),
							),
						);

						if( !empty($product->get_sku()) ) {
							$filtergps['filterGroups'][] = array(
								'filters' => array(
									array(
										'value' => $product->get_sku(),
										'propertyName' => 'hs_sku',
										'operator' => 'EQ',
									),
								),
							);
						}

						$response = HubWooConnectionMananager::get_instance()->search_object_record( 'products', $filtergps );

						if ( 200 == $response['status_code'] ) {
							$responce_body = json_decode( $response['body'] );
							$result = $responce_body->results;
							if ( ! empty( $result ) ) {
								foreach ( $result as $key => $value ) {
									update_post_meta( $pro_id, 'hubwoo_ecomm_pro_id', $value->id );
								}
							}
						}

						$pro_hs_id = get_post_meta( $pro_id, 'hubwoo_ecomm_pro_id', true );

						if ( ! empty( $pro_hs_id ) ) {
							$response = HubWooConnectionMananager::get_instance()->update_object_record( 'products', $pro_hs_id, $product_data );
							if ( 200 == $response['status_code'] ) {
								delete_post_meta( $pro_id, 'hubwoo_product_synced' );
							}
						} else {
							$response = HubWooConnectionMananager::get_instance()->create_object_record( 'products', $product_data );
							if ( 201 == $response['status_code'] ) {
								$response_body = json_decode( $response['body'] );
								update_post_meta( $pro_id, 'hubwoo_ecomm_pro_id', $response_body->id );
								delete_post_meta( $pro_id, 'hubwoo_product_synced' );
							}
						}
						do_action( 'hubwoo_update_product_property', $pro_id );
					}
				}

				if ( 'final_request' == $pro_get_vid ) {
					update_option( 'hubwoo_ecomm_setup_completed', 'yes' );
				}

				echo wp_json_encode(
					array(
						'step'        => $step + 1,
						'status'      => true,
						'total_deals' => empty( $order_ids ) ? '' : count( $order_ids ),
						'response'    => 'Products batch synced succesfully',
					)
				);
				wp_die();
			}
		}

		/**
		 * Sync historical deals to HubSpot.
		 */
		public function hubwoo_historical_deals_sync() {

			// Nonce verification.
			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			$object_type  = 'DEAL';
			$deal_updates = array();

			$step = ! empty( $_POST['step'] ) ? sanitize_text_field( wp_unslash( $_POST['step'] ) ) : '';

			if ( ! empty( $_POST['max_item'] ) ) {
				$max_item = sanitize_text_field( wp_unslash( $_POST['max_item'] ) );
			} else {
				$max_item = 15;
			}

			if( Hubwoo::hubwoo_check_hpos_active() ) {
				$query = new WC_Order_Query(array(
					'posts_per_page'      => $max_item,
					'post_status'         => array_keys( wc_get_order_statuses() ),
					'orderby'             => 'date',
					'order'               => 'desc',
					'return'              => 'ids',
					'no_found_rows'       => true,
					'ignore_sticky_posts' => true,
					'post_parent'         => 0,
					'meta_key'			  => 'hubwoo_ecomm_deal_created',
					'meta_compare'		  => 'NOT EXISTS',
				));

				$order_ids = $query->get_orders();
			} else {

				$query = new WP_Query();

				$order_args = array(
					'post_type'           => 'shop_order',
					'posts_per_page'      => $max_item,
					'post_status'         => array_keys( wc_get_order_statuses() ),
					'orderby'             => 'date',
					'order'               => 'desc',
					'fields'              => 'ids',
					'no_found_rows'       => true,
					'ignore_sticky_posts' => true,
					'meta_query'          => array(
						array(
							'key'     => 'hubwoo_ecomm_deal_created',
							'compare' => 'NOT EXISTS',
						),
					),
				);

				$order_ids = $query->query( $order_args );
			}

			if ( empty( $order_ids ) ) {

				echo wp_json_encode(
					array(
						'step'     => $step + 1,
						'status'   => true,
						'response' => 'No deals found to sync.',
					)
				);
				wp_die();

			}

			foreach ( $order_ids as $order_id ) {

				$order = wc_get_order( $order_id );
				$deal_updates = array();

				if ( $order instanceof WC_Order ) {

					$customer_id = $order->get_customer_id();

					if ( ! empty( $customer_id ) ) {
						$source = 'user';
						HubwooObjectProperties::hubwoo_ecomm_contacts_with_id( $customer_id );

					} else {
						$source = 'guest';
						$guest_object_type = 'CONTACT';

						$guest_user_properties = $this->mwb_get_guestuser_properties( $order_id );
						$contact_properties = array();
						foreach ( $guest_user_properties as $key => $value ) {

							$contact_properties[ $value['property'] ] = $value['value'];
						}
						$guest_contact_properties = apply_filters( 'hubwoo_map_ecomm_guest_' . $guest_object_type . '_properties', $contact_properties, $order_id );

						$contacts = array();
						if ( isset( $order_id ) ) {
							//hpos changes
							$user_data                              = array();
							$user_data['email']                     = $order->get_billing_email();
							$user_data['customer_group']            = 'guest';
							$user_data['firstname']                 = $order->get_billing_first_name();
							$user_data['lastname']                  = $order->get_billing_last_name();
							$user_data['customer_source_store']     = get_bloginfo( 'name' );
							$user_data['hs_language']        		= $order->get_meta('hubwoo_preferred_language', true);
							foreach ( $guest_contact_properties as $key => $value ) {
								$user_data[ $key ] = $value;
							}
							$user_vid                   = $order->get_meta('hubwoo_user_vid', true);
							$contacts = array(
								'properties' => $user_data,
							);
						}

						if ( ! empty( $contacts ) ) {

							$flag = true;

							if ( Hubwoo::is_access_token_expired() ) {

								$hapikey = HUBWOO_CLIENT_ID;
								$hseckey = HUBWOO_SECRET_ID;
								$status  = HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey );

								if ( ! $status ) {

									$flag = false;
								}
							}

							if ( $flag ) {

								if ( ! empty( $user_vid ) ) {
									$response = HubWooConnectionMananager::get_instance()->update_object_record( 'contacts', $user_vid, $contacts );
								} else {
									$response = HubWooConnectionMananager::get_instance()->create_object_record( 'contacts', $contacts );

									if ( 201 == $response['status_code'] ) {
										$contact_vid = json_decode( $response['body'] );
										$order->update_meta_data('hubwoo_user_vid', $contact_vid->id);
										$order->update_meta_data('hubwoo_pro_guest_order', 'synced');
										$order->save();

									} else if ( 409 == $response['status_code'] ) {
										$contact_vid = json_decode( $response['body'] );
										$hs_id = explode( 'ID: ', $contact_vid->message );
										$response = HubWooConnectionMananager::get_instance()->update_object_record( 'contacts', $hs_id[1], $contacts );
										$order->update_meta_data('hubwoo_user_vid', $hs_id[1]);
										$order->update_meta_data('hubwoo_pro_guest_order', 'synced');
										$order->save();
									} else if ( 400 == $response['status_code'] ) {
										$order->update_meta_data('hubwoo_invalid_contact', 'yes');
										$order->update_meta_data('hubwoo_pro_user_data_change', 'synced');
										$order->update_meta_data('hubwoo_pro_guest_order', 'synced');
										$order->save();
									}
								}
								
								$user_email = $order->get_billing_email();
								do_action( 'hubwoo_ecomm_contact_synced', $user_email );
							}
						}
					}
				}

				$assc_deal_cmpy              = get_option( 'hubwoo_assoc_deal_cmpy_enable', 'yes' );
				$pipeline_id                 = get_option( 'hubwoo_ecomm_pipeline_id', false );
				$hubwoo_ecomm_deal           = new HubwooEcommObject( $order_id, $object_type );
				$deal_properties             = $hubwoo_ecomm_deal->get_object_properties();
				$deal_properties             = apply_filters( 'hubwoo_map_ecomm_' . $object_type . '_properties', $deal_properties, $order_id );

				if ( 'yes' == get_option( 'hubwoo_deal_multi_currency_enable', 'no' ) ) {
					$currency = $order->get_currency();
					if ( ! empty( $currency ) ) {
						$deal_properties['deal_currency_code'] = $currency;
					}
				}

				if ( empty( $pipeline_id ) ) {
					Hubwoo::get_all_deal_stages();
					$pipeline_id = get_option( 'hubwoo_ecomm_pipeline_id', false );
				}

				$deal_properties['pipeline'] = $pipeline_id;

				$deal_updates   = array(
					'properties' => $deal_properties,
				);
				$response          = '';

				if ( 'user' == $source ) {
					$user_info  = json_decode( wp_json_encode( get_userdata( $customer_id ) ), true );
					$user_email = $user_info['data']['user_email'];
					$contact    = $user_email;
					if ( empty( $contact ) ) {
						$contact = $customer_id;
					}
					$contact_vid = get_user_meta( $customer_id, 'hubwoo_user_vid', true );
					$invalid_contact = get_user_meta( $customer_id, 'hubwoo_invalid_contact', true );
				} else {
					$contact_vid = $order->get_meta('hubwoo_user_vid', true);
					$contact = $order->get_billing_email();
					$invalid_contact = $order->get_meta('hubwoo_invalid_contact', true);
				}

				if ( count( $deal_updates ) ) {

					$flag = true;
					if ( Hubwoo::is_access_token_expired() ) {

						$hapikey = HUBWOO_CLIENT_ID;
						$hseckey = HUBWOO_SECRET_ID;
						$status  = HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey );

						if ( ! $status ) {

							$flag = false;
						}
					}

					if ( $flag ) {

						$deal_name  = '#' . $order->get_order_number();

						$user_detail['first_name'] = $order->get_billing_first_name();
						$user_detail['last_name']  = $order->get_billing_last_name();

						foreach ( $user_detail as $value ) {
							if ( ! empty( $value ) ) {
								$deal_name .= ' ' . $value;
							}
						}

						$filtergps = array(
							'filterGroups' => array(
								array(
									'filters' => array(
										array(
											'value' => $deal_name,
											'propertyName' => 'dealname',
											'operator' => 'EQ',
										),
									),
								),
							),
						);

						$response = HubWooConnectionMananager::get_instance()->search_object_record( 'deals', $filtergps );

						if ( 200 == $response['status_code'] ) {
							$responce_body = json_decode( $response['body'] );
							$result = $responce_body->results;
							if ( ! empty( $result ) ) {
								foreach ( $result as $key => $value ) {
									$order->update_meta_data('hubwoo_ecomm_deal_id', $value->id);
									$order->update_meta_data('hubwoo_order_line_item_created', 'yes');
									$order->save();
								}
							}
						}

						$hubwoo_ecomm_deal_id = $order->get_meta('hubwoo_ecomm_deal_id', true);

						if ( empty( $hubwoo_ecomm_deal_id ) ) {
							$response = HubWooConnectionMananager::get_instance()->create_object_record( 'deals', $deal_updates );
							if ( 201 == $response['status_code'] ) {
								$response_body = json_decode( $response['body'] );
								$hubwoo_ecomm_deal_id = $response_body->id;
								$order->update_meta_data('hubwoo_ecomm_deal_id', $hubwoo_ecomm_deal_id );
								$order->save();
							}
						} else {
							$response = HubWooConnectionMananager::get_instance()->update_object_record( 'deals', $hubwoo_ecomm_deal_id, $deal_updates );
						}

						HubWooConnectionMananager::get_instance()->associate_object( 'deal', $hubwoo_ecomm_deal_id, 'contact', $contact_vid, 3 );

						do_action( 'hubwoo_ecomm_deal_created', $order_id );

						if ( 'yes' == $assc_deal_cmpy ) {
							if ( ! empty( $contact ) && empty( $invalid_contact ) ) {
								Hubwoo::hubwoo_associate_deal_company( $contact, $hubwoo_ecomm_deal_id );
							}
						}
						$this->bulk_line_item_link( $order_id );
					}
				}
			}

			echo wp_json_encode(
				array(
					'step'     => $step + 1,
					'status'   => true,
					'response' => 'Deals batch synced succesfully',
				)
			);
			wp_die();

		}

		/**
		 * Hide review notice.
		 */
		public function hubwoo_hide_rev_notice() {

			// Nonce verification.
			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			update_option( 'hubwoo_hide_rev_notice', 'yes' );

			echo wp_json_encode(
				array(
					'status'   => true,
					'response' => 'Notice hide succesfully',
				)
			);
			wp_die();
		}

		/**
		 * Hide HPOS notice
		 */
		public function hubwoo_hide_hpos_notice(){
			// Nonce verification.
			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			update_option( 'hubwoo_hide_hpos_notice', 'yes' );

			echo wp_json_encode(
				array(
					'status'   => true,
					'response' => 'Notice hide succesfully',
				)
			);
			wp_die();
		}

		/**
		 * Get guest user properties.
		 *
		 * @param int $order_id Order ID.
		 */
		public function mwb_get_guestuser_properties( $order_id ) {

			global $hubwoo;

			$hubwoo_guest_order = wc_get_order( $order_id );

			if ( $hubwoo_guest_order instanceof WC_Order ) {
				//hpos changes
				$guest_email = $hubwoo_guest_order->get_billing_email();

				$guest_order_callback = new HubwooGuestOrdersManager( $order_id );

				$guest_user_properties = $guest_order_callback->get_order_related_properties( $order_id, $guest_email );

				$guest_user_properties = $hubwoo->hubwoo_filter_contact_properties( $guest_user_properties );

				$fname = $hubwoo_guest_order->get_billing_first_name();
				if ( ! empty( $fname ) ) {
					$guest_user_properties[] = array(
						'property' => 'firstname',
						'value'    => $fname,
					);
				}

				$lname = $hubwoo_guest_order->get_billing_last_name();
				if ( ! empty( $lname ) ) {
					$guest_user_properties[] = array(
						'property' => 'lastname',
						'value'    => $lname,
					);
				}

				$cname = $hubwoo_guest_order->get_billing_company();
				if ( ! empty( $cname ) ) {
					$guest_user_properties[] = array(
						'property' => 'company',
						'value'    => $cname,
					);
				}

				$city = $hubwoo_guest_order->get_billing_city();
				if ( ! empty( $city ) ) {
					$guest_user_properties[] = array(
						'property' => 'city',
						'value'    => $city,
					);
				}

				$state = $hubwoo_guest_order->get_billing_state();
				if ( ! empty( $state ) ) {
					$guest_user_properties[] = array(
						'property' => 'state',
						'value'    => $state,
					);
				}

				$country = $hubwoo_guest_order->get_billing_country();
				if ( ! empty( $country ) ) {
					$guest_user_properties[] = array(
						'property' => 'country',
						'value'    => Hubwoo::map_country_by_abbr( $country ),
					);
				}

				$address1 = $hubwoo_guest_order->get_billing_address_1();
				$address2 = $hubwoo_guest_order->get_billing_address_2();
				if ( ! empty( $address1 ) || ! empty( $address2 ) ) {
					$address                 = $address1 . ' ' . $address2;
					$guest_user_properties[] = array(
						'property' => 'address',
						'value'    => $address,
					);
				}

				$zip = $hubwoo_guest_order->get_billing_postcode();
				if ( ! empty( $zip ) ) {
					$guest_user_properties[] = array(
						'property' => 'zip',
						'value'    => $zip,
					);
				}

				$guest_phone = $hubwoo_guest_order->get_billing_phone();

				if ( ! empty( $guest_phone ) ) {
					$guest_user_properties[] = array(
						'property' => 'mobilephone',
						'value'    => $guest_phone,
					);
					$guest_user_properties[] = array(
						'property' => 'phone',
						'value'    => $guest_phone,
					);
				}

				$customer_new_order_flag = 'no';
				$prop_index              = array_search( 'customer_new_order', array_column( $guest_user_properties, 'property' ) );

				if ( Hubwoo_Admin::hubwoo_check_for_properties( 'order_recency_rating', 5, $guest_user_properties ) ) {

					if ( Hubwoo_Admin::hubwoo_check_for_properties( 'last_order_status', get_option( 'hubwoo_no_status', 'wc-completed' ), $guest_user_properties ) ) {

						$customer_new_order_flag = 'yes';
					}
				}

				if ( $prop_index ) {
					$guest_user_properties[ $prop_index ]['value'] = $customer_new_order_flag;
				} else {
					$guest_user_properties[] = array(
						'property' => 'customer_new_order',
						'value'    => $customer_new_order_flag,
					);
				}
			}
			return $guest_user_properties;
		}

		/**
		 * Sync line items to deals over Hubspot.
		 *
		 * @param int $order_id Order ID.
		 */
		public function bulk_line_item_link( $order_id ) {

			if ( ! empty( $order_id ) ) {
				$object_type       = 'LINE_ITEM';
				$order             = wc_get_order( $order_id );
				$line_updates      = array();
				$order_items       = $order->get_items();
				$object_ids        = array();
				$response          = array( 'status_code' => 206 );
				$no_products_found = false;

				if ( is_array( $order_items ) && count( $order_items ) ) {

					foreach ( $order_items as $item_key => $single_item ) :

						$product_id = $single_item->get_variation_id();
						if ( 0 === $product_id ) {
							$product_id = $single_item->get_product_id();
							if ( 0 === $product_id ) {
								$no_products_found = true;
							}
						}
						if ( get_post_status( $product_id ) == 'trash' || get_post_status( $product_id ) == false ) {
							continue;
						}
						$item_sku = get_post_meta( $product_id, '_sku', true );
						if ( empty( $item_sku ) ) {
							$item_sku = $product_id;
						}

						$line_item_hs_id = wc_get_order_item_meta( $item_key, 'hubwoo_ecomm_line_item_id', true );

						if ( ! empty( $line_item_hs_id ) || 'yes' == $order->get_meta('hubwoo_order_line_item_created', 'no') ) {
							continue;
						}

						$quantity        = ! empty( $single_item->get_quantity() ) ? $single_item->get_quantity() : 0;
						$item_total      = ! empty( $single_item->get_total() ) ? $single_item->get_total() : 0;
						$item_sub_total  = ! empty( $single_item->get_subtotal() ) ? $single_item->get_subtotal() : 0;
						$product         = $single_item->get_product();
						$name            = HubwooObjectProperties::get_instance()->hubwoo_ecomm_product_name( $product );
						$discount_amount = abs( $item_total - $item_sub_total );
						$discount_amount = $discount_amount / $quantity;
						$item_sub_total  = $item_sub_total / $quantity;
						$hs_product_id   = get_post_meta( $product_id, 'hubwoo_ecomm_pro_id', true );
						$object_ids[]    = $item_key;

						$properties = array(
							'quantity'        => $quantity,
							'price'           => $item_sub_total,
							'amount'          => $item_total,
							'name'            => $name,
							'discount_amount' => $discount_amount,
							'sku'             => $item_sku,
							'tax_amount'      => $single_item->get_total_tax(),
						);

						if ( 'yes' != get_option( 'hubwoo_product_scope_needed', 'no' ) ) {
							$properties['hs_product_id'] = $hs_product_id;
						}

						$properties = apply_filters( 'hubwoo_line_item_properties', $properties, $product_id, $order_id );

						$line_updates[] = array(
							'properties'       => $properties,
						);
					endforeach;
				}

				$line_updates = apply_filters( 'hubwoo_custom_line_item', $line_updates, $order_id );

				if ( count( $line_updates ) ) {
					$line_updates = array(
						'inputs' => $line_updates,
					);

					$flag = true;
					if ( Hubwoo::is_access_token_expired() ) {
						$hapikey = HUBWOO_CLIENT_ID;
						$hseckey = HUBWOO_SECRET_ID;
						$status  = HubWooConnectionMananager::get_instance()->hubwoo_refresh_token( $hapikey, $hseckey );
						if ( ! $status ) {
							$flag = false;
						}
					}
					if ( $flag ) {

						$response = HubWooConnectionMananager::get_instance()->create_batch_object_record( 'line_items', $line_updates );
					}
				}

				if ( 201 == $response['status_code'] || 204 == $response['status_code'] || empty( $object_ids ) ) {

					$order->update_meta_data( 'hubwoo_ecomm_deal_created', 'yes' );
					$order->save();

					$deal_id = $order->get_meta( 'hubwoo_ecomm_deal_id', true );
					if ( isset( $response['body'] ) && ! empty( $response['body'] ) ) {
						$response_body = json_decode( $response['body'] );
						foreach ( $order_items as $item_key => $single_item ) :

							$product_id = $single_item->get_variation_id();
							if ( 0 === $product_id ) {
								$product_id = $single_item->get_product_id();
								if ( 0 === $product_id ) {
									$no_products_found = true;
								}
							}
							if ( get_post_status( $product_id ) == 'trash' || get_post_status( $product_id ) == false ) {
								continue;
							}

							$product         = $single_item->get_product();
							$name            = HubwooObjectProperties::get_instance()->hubwoo_ecomm_product_name( $product );

							if ( isset( $response_body ) && ! empty( $response_body ) ) {

								foreach ( $response_body->results as $key => $value ) {

									$line_item_hs_id = $value->id;
									$order->update_meta_data( 'hubwoo_order_line_item_created', 'yes' );
									$order->save();
									$response = HubWooConnectionMananager::get_instance()->associate_object( 'deal', $deal_id, 'line_item', $line_item_hs_id, 19 );
								}
							}
						endforeach;

						if ( 1 == get_option( 'hubwoo_deals_sync_running', 0 ) ) {

							$current_count = get_option( 'hubwoo_deals_current_sync_count', 0 );
							update_option( 'hubwoo_deals_current_sync_count', ++$current_count );
						}
					}
				}
			}
		}

		/**
		 * Fetch logs from database.
		 */
		public function hubwoo_get_datatable_data() {
			// Nonce verification.
			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			$request = $_GET;
			$offset  = $request['start'];
			$limit   = $request['length'];

			$search_data  = $request['search'];
			$search_value = $search_data['value'];

			if ( '' !== $search_value ) {
				$log_data = Hubwoo::hubwoo_get_log_data( $search_value );
			} else {

				$log_data = Hubwoo::hubwoo_get_log_data( false, $limit, $offset );
			}

			$count_data  = Hubwoo::hubwoo_get_total_log_count();
			$total_count = $count_data[0];
			$data        = array();
			foreach ( $log_data as $key => $value ) {
				$value[ Hubwoo::get_current_crm_name( 'slug' ) . '_object' ] = ! empty( $value[ Hubwoo::get_current_crm_name( 'slug' ) . '_object' ] ) ? $value[ Hubwoo::get_current_crm_name( 'slug' ) . '_object' ] : '-';
				$value[ Hubwoo::get_current_crm_name( 'slug' ) . '_id' ]     = ! empty( $value[ Hubwoo::get_current_crm_name( 'slug' ) . '_id' ] ) ? $value[ Hubwoo::get_current_crm_name( 'slug' ) . '_id' ] : '-';

				$current_request  = $value['request']; //phpcs:ignore
				$response = unserialize( $value['response'] ); //phpcs:ignore

				$temp = array(
					'',
					$value['event'],
					$value[ Hubwoo::get_current_crm_name( 'slug' ) . '_object' ],
					gmdate( 'd-m-Y h:i A', esc_html( $value['time'] ) ),
					$current_request,
					wp_json_encode( $response ),
				);

				$data[] = $temp;
			}

			$json_data = array(
				'draw'            => intval( $request['draw'] ),
				'recordsTotal'    => $total_count,
				'recordsFiltered' => $total_count,
				'data'            => $data,
			);

			echo wp_json_encode( $json_data );
			wp_die();
		}

		/**
		 * Download logs from database.
		 */
		public function hubwoo_download_sync_log() {
			// Nonce verification.
			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			$crm_name = Hubwoo::get_current_crm_name( 'slug' );
			$log_data = Hubwoo::hubwoo_get_log_data( false, 25, 0, true );
			$data     = array();
			$log_dir  = WC_LOG_DIR . $crm_name . '-sync-log.log';
			if ( ! is_dir( $log_dir ) ) {
				$log_dir = WC_LOG_DIR . $crm_name . '-sync-log.log';
			}

			global $wp_filesystem;  // Define global object of WordPress filesystem.
			WP_Filesystem();        // Intialise new file system object.

			$file_data = '';
			$wp_filesystem->put_contents( $log_dir, '' );

			foreach ( $log_data as $key => $value ) {
				$value[ $crm_name . '_id' ] = ! empty( $value[ $crm_name . '_id' ] ) ? $value[ $crm_name . '_id' ] : '-';
				$log                        = 'Feed : ' . $value['event'] . PHP_EOL;
				$log                       .= ucwords( $crm_name ) . ' Object : ' . $value[ $crm_name . '_object' ] . PHP_EOL;
				$log                       .= 'Time : ' . gmdate( 'd-m-Y h:i A', esc_html( $value['time'] ) ) . PHP_EOL;
				$log                       .= 'Request : ' . $value['request'] . PHP_EOL; // phpcs:ignore
				$log                       .= 'Response : ' . wp_json_encode( unserialize( $value['response'] ) ) . PHP_EOL;  // phpcs:ignore
				$log                       .= '-----------------------------------------------------------------------' . PHP_EOL;

				$file_data .= $log;
				$wp_filesystem->put_contents( $log_dir, $file_data );
			}

			$json_data = array(
				'success'  => true,
				'redirect' => admin_url( 'admin.php?page=hubwoo&hubwoo_tab=hubwoo-logs&hubwoo_download=1' ),
			);

			echo wp_json_encode( $json_data );
			wp_die();
		}

		/**
		 * Clear log from database
		 */
		public function hubwoo_clear_sync_log() {
			// Nonce verification.
			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			global $wpdb;
			$table_name       = $wpdb->prefix . 'hubwoo_log';
			
			$wpdb->get_results( $wpdb->prepare( 'DELETE FROM %1s', $table_name ) );

			$json_data = array(
				'success'  => true,
				'redirect' => admin_url( 'admin.php?page=hubwoo&hubwoo_tab=hubwoo-logs' ),
			);

			echo wp_json_encode( $json_data );
			wp_die();
		}

		/**
		 * Fetch pipeline deal stages.
		 */
		public function hubwoo_fetch_deal_stages() {
			// Nonce verification.
			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			$selected_pipeline = ! empty( $_POST['selected_pipeline'] ) ? sanitize_text_field( wp_unslash( $_POST['selected_pipeline'] ) ) : '';

			$all_pipeline = get_option( 'hubwoo_potal_pipelines', true );
			update_option( 'hubwoo_ecomm_pipeline_id', $selected_pipeline );
			$deal_stages = '';

			foreach ( $all_pipeline as $single_pipeline ) {
				if ( $single_pipeline['id'] == $selected_pipeline ) {
					$deal_stages = $single_pipeline['stages'];
					if ( 'Ecommerce Pipeline' == $single_pipeline['label'] ) {
						Hubwoo::update_deal_stages_mapping( $deal_stages );
					} else if ( 'Sales Pipeline' == $single_pipeline['label'] ) {
						update_option( 'hubwoo_ecomm_final_mapping', Hubwoo::hubwoo_sales_deals_mapping() );
					}
				}
			}

			if ( ! empty( $deal_stages ) ) {

				update_option( 'hubwoo_ecomm_pipeline_created', 'yes' );
				update_option( 'hubwoo_fetched_deal_stages', $deal_stages );
				update_option( 'hubwoo_ecomm_won_stages', '' );
			}

			echo wp_json_encode(
				array(
					'success'  => true,
				)
			);
			wp_die();
		}

		/**
		 * Fetch deal pipelines.
		 */
		public function hubwoo_fetch_update_pipelines() {
			// Nonce verification.
			check_ajax_referer( 'hubwoo_security', 'hubwooSecurity' );

			$selected_pipeline = ! empty( $_POST['selected_pipeline'] ) ? sanitize_text_field( wp_unslash( $_POST['selected_pipeline'] ) ) : '';

			$all_deal_pipelines = HubWooConnectionMananager::get_instance()->fetch_all_deal_pipelines();

			if ( ! empty( $all_deal_pipelines['results'] ) ) {
				update_option( 'hubwoo_potal_pipelines', $all_deal_pipelines['results'] );
			}

			$all_pipeline = get_option( 'hubwoo_potal_pipelines', true );
			update_option( 'hubwoo_ecomm_pipeline_id', $selected_pipeline );
			$deal_stages = '';

			foreach ( $all_pipeline as $single_pipeline ) {
				if ( $single_pipeline['id'] == $selected_pipeline ) {
					$deal_stages = $single_pipeline['stages'];
				}
			}

			if ( ! empty( $deal_stages ) ) {

				update_option( 'hubwoo_ecomm_pipeline_created', 'yes' );
				update_option( 'hubwoo_fetched_deal_stages', $deal_stages );
				update_option( 'hubwoo_ecomm_won_stages', '' );
			}

			echo wp_json_encode(
				array(
					'success'  => true,
				)
			);
			wp_die();
		}
	}
}

new Hubwoo_Ajax_Handler();
