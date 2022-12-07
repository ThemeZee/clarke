<?php
/**
 * Clarke License Settings
 *
 * @package Clarke
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Clarke_License_Settings {

	/**
	 * Setup the Clarke Licene Settings class
	 */
	static function setup() {

		// Register settings.
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );

		// Add License API functions.
		add_action( 'admin_init', array( __CLASS__, 'activate_license' ) );
		add_action( 'admin_init', array( __CLASS__, 'deactivate_license' ) );

		// Add Admin notices.
		add_action( 'admin_notices', array( __CLASS__, 'license_activated_notice' ) );
		add_action( 'admin_notices', array( __CLASS__, 'theme_page_notice' ) );
	}

	/**
	 * Get settings
	 *
	 * @return array
	 */
	static function get_settings() {
		$default_settings = array(
			'clarke_license_key'    => '',
			'clarke_license_status' => 'inactive',
		);

		return wp_parse_args( get_option( 'clarke_theme_settings', array() ), $default_settings );
	}

	/**
	 * Register all settings sections and fields
	 */
	static function register_settings() {

		// Add License Status Setting.
		add_settings_field(
			'clarke_theme_settings[clarke_license_status]',
			esc_html__( 'License Status', 'clarke' ),
			array( __CLASS__, 'render_license_status_setting' ),
			'clarke_theme_settings',
			'clarke_license_section'
		);

		// Add License Key Setting.
		add_settings_field(
			'clarke_theme_settings[clarke_license_key]',
			esc_html__( 'License Key', 'clarke' ),
			array( __CLASS__, 'render_license_key_setting' ),
			'clarke_theme_settings',
			'clarke_license_section'
		);
	}

	/**
	 * License Status Callback
	 */
	static function render_license_status_setting() {
		$options        = self::get_settings();
		$license_status = $options['clarke_license_status'];
		$license_key    = ! empty( $options['clarke_license_key'] ) ? trim( $options['clarke_license_key'] ) : false;

		$html = '';
		if ( 'valid' === $license_status && ! empty( $license_key ) ) {
			$html .= '<span style="display: inline-block; margin-bottom: 12px; padding: 4px 8px; background: green; color: #fff;">' . esc_html__( 'Active', 'clarke' ) . '</span>';
			$html .= '<br/><span class="description">' . esc_html__( 'You are receiving updates.', 'clarke' ) . '</span>';
		} else {
			$html .= '<span style="display: inline-block; margin-bottom: 12px; padding: 4px 8px; background: #444; color: #fff;">' . esc_html__( 'Inactive', 'clarke' ) . '</span>';
			$html .= '<br/><span class="description">' . esc_html__( 'Please activate your license.', 'clarke' ) . '</span>';
		}

		echo $html;
	}

	/**
	 * License Key Callback
	 */
	static function render_license_key_setting() {
		$options        = self::get_settings();
		$license_status = $options['clarke_license_status'];
		$license_key    = ! empty( $options['clarke_license_key'] ) ? trim( $options['clarke_license_key'] ) : false;

		$html = '';
		if ( 'valid' === $license_status && ! empty( $license_key ) ) {
			$html .= '<span style="display: inline-block; box-sizing: border-box; width: 25em; margin: 0 1px; padding: 0 8px;line-height: 2;border-radius: 4px;border: 1px solid #8c8f94;">*************************' . esc_html( substr( stripslashes( $license_key ), 25 ) ) . '</span>';
			$html .= '<input type="submit" class="button" name="clarke_deactivate_license" value="' . esc_attr__( 'Deactivate License', 'clarke' ) . '"/>';
		} else {
			$html .= '<input type="text" class="regular-text" id="clarke_theme_settings[clarke_license_key]" name="clarke_theme_settings[clarke_license_key]" value="' . esc_attr( stripslashes( $license_key ) ) . '"/>';
			$html .= '<input type="submit" class="button" name="clarke_activate_license" value="' . esc_attr__( 'Activate License', 'clarke' ) . '"/>';
		}

		echo $html;
		wp_nonce_field( 'clarke_license_nonce', 'clarke_license_nonce' );
	}

	/**
	 * Activates the license key.
	 */
	static function activate_license() {

		// Return early if not on Clarke admin page.
		if ( ! isset( $_POST['clarke_theme_settings'] ) ) {
			return;
		}

		// Listen for our activate button to be clicked.
		if ( ! isset( $_POST['clarke_activate_license'] ) ) {
			return;
		}

		// Run a quick security check.
		if ( ! check_admin_referer( 'clarke_license_nonce', 'clarke_license_nonce' ) ) {
			return;
		}

		// Get License key.
		$license_key = ! empty( $_POST['clarke_theme_settings']['clarke_license_key'] ) ? sanitize_text_field( $_POST['clarke_theme_settings']['clarke_license_key'] ) : false;

		// Return if no license key was entered.
		if ( ! $license_key ) {
			return;
		}

		// Data to send in our API request.
		$api_params = array(
			'edd_action'  => 'activate_license',
			'license'     => $license_key,
			'item_id'     => CLARKE_THEME_ID,
			'item_name'   => rawurlencode( CLARKE_THEME_NAME ),
			'url'         => home_url(),
			'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
		);

		// Call the custom API.
		$response = wp_remote_post(
			CLARKE_THEME_STORE_URL,
			array(
				'timeout'   => 15,
				'sslverify' => true,
				'body'      => $api_params,
			)
		);

		// Make sure the response came back okay.
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.', 'clarke' );
			}
		} else {

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( false === $license_data->success ) {

				switch ( $license_data->error ) {

					case 'expired':
						$message = sprintf(
							/* translators: the license key expiration date */
							__( 'Your license key expired on %s.', 'clarke' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
						);
						break;

					case 'disabled':
					case 'revoked':
						$message = __( 'Your license key has been disabled.', 'clarke' );
						break;

					case 'missing':
						$message = __( 'Invalid license.', 'clarke' );
						break;

					case 'invalid':
					case 'site_inactive':
						$message = __( 'Your license is not active for this URL.', 'clarke' );
						break;

					case 'item_name_mismatch':
						/* translators: the plugin name */
						$message = sprintf( __( 'This appears to be an invalid license key for %s.', 'clarke' ), CLARKE_THEME_NAME );
						break;

					case 'no_activations_left':
						$message = __( 'Your license key has reached its activation limit.', 'clarke' );
						break;

					default:
						$message = __( 'An error occurred, please try again.', 'clarke' );
						break;
				}
			}
		}

		// Check if anything passed on a message constituting a failure.
		if ( ! empty( $message ) ) {
			$redirect = add_query_arg(
				array(
					'page'              => 'clarke-theme',
					'clarke_activation' => 'false',
					'message'           => rawurlencode( $message ),
				),
				admin_url( 'themes.php' )
			);

			wp_safe_redirect( $redirect );
			exit();
		}

		// Retrieve the license from the database.
		$options = self::get_settings();

		// $license_data->license will be either "valid" or "invalid".
		if ( 'valid' === $license_data->license ) {
			$options['clarke_license_key'] = $license_key;
		}
		$options['clarke_license_status'] = $license_data->license;
		update_option( 'clarke_theme_settings', $options );

		wp_safe_redirect( admin_url( 'themes.php?page=clarke-theme' ) );
		exit();
	}

	/**
	 * Deactivates the license key.
	 * This will decrease the site count.
	 */
	static function deactivate_license() {

		// Listen for our activate button to be clicked.
		if ( ! isset( $_POST['clarke_deactivate_license'] ) ) {
			return;
		}

		// Run a quick security check.
		if ( ! check_admin_referer( 'clarke_license_nonce', 'clarke_license_nonce' ) ) {
			return;
		}

		// Retrieve our license key from the DB.
		$options     = self::get_settings();
		$license_key = ! empty( $options['clarke_license_key'] ) ? trim( $options['clarke_license_key'] ) : false;

		// data to send in our API request
		$api_params = array(
			'edd_action'  => 'deactivate_license',
			'license'     => $license_key,
			'item_id'     => CLARKE_THEME_ID,
			'item_name'   => rawurlencode( CLARKE_THEME_NAME ), // the name of our product in EDD
			'url'         => home_url(),
			'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
		);

		// Call the custom API.
		$response = wp_remote_post(
			CLARKE_THEME_STORE_URL,
			array(
				'timeout'   => 15,
				'sslverify' => true,
				'body'      => $api_params,
			)
		);

		// Make sure the response came back okay.
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.', 'clarke' );
			}

			$redirect = add_query_arg(
				array(
					'page'                  => 'clarke-theme',
					'clarke_activation' => 'false',
					'message'               => rawurlencode( $message ),
				),
				admin_url( 'themes.php' )
			);

			wp_safe_redirect( $redirect );
			exit();
		}

		// Deactivate the License key in DB.
		$options['clarke_license_key']    = '';
		$options['clarke_license_status'] = 'inactive';
		update_option( 'clarke_theme_settings', $options );

		wp_safe_redirect( admin_url( 'themes.php?page=clarke-theme' ) );
		exit();
	}

	/**
	 * This is a means of catching errors from the activation method above and displaying it to the customer
	 */
	static function license_activated_notice() {
		if ( isset( $_GET['clarke_activation'] ) && ! empty( $_GET['message'] ) ) {

			switch ( $_GET['clarke_activation'] ) {

				case 'false':
					$message = urldecode( $_GET['message'] );
					?>
					<div class="error">
						<p><?php echo wp_kses_post( $message ); ?></p>
					</div>
					<?php
					break;

				case 'true':
				default:
					// Developers can put a custom success message here for when activation is successful if they way.
					break;

			}
		}
	}

	/**
	 * Display activate license notice
	 *
	 * @return void
	 */
	static function theme_page_notice() {
		global $pagenow;
		$options = self::get_settings();

		if ( 'valid' !== $options['clarke_license_status'] && in_array( $pagenow, array( 'index.php', 'update-core.php', 'themes.php' ) ) && ! isset( $_GET['page'] ) && current_user_can( 'manage_options' ) ) :
			?>

			<div class="notice notice-info">
				<p>
					<?php
					printf( __( 'Please enter your license key for the %1$s theme in order to receive updates and support. <a href="%2$s">Enter License Key</a>', 'clarke' ),
						'Clarke',
						admin_url( 'themes.php?page=clarke-theme' )
					);
					?>
				</p>
			</div>

			<?php
		endif;
	}
}

// Run class.
Clarke_License_Settings::setup();
