<?php
/**
 * Handles the admin interface and functionality for license key management in the DHWP Starter Plugin.
 *
 * This class integrates with the WooCommerce REST API to activate and validate license keys,
 * adds an admin menu for license management, and handles form submissions for license activation.
 *
 * @package DHWP_Starter_Plugin
 */
class DHWP_License_Admin {
    /**
     * WooCommerce API consumer key for authentication.
     *
     * @var string
     */
    private $api_consumer_key = 'ck_bc189654b5f68388ff05f8ccb252a770dea8ea0a';

    /**
     * WooCommerce API consumer secret for authentication.
     *
     * @var string
     */
    private $api_consumer_secret = 'cs_5e0a0c7567dc9a76fce16e7442557653808329f5';

    /**
     * Constructor to initialize WordPress hooks for the license admin functionality.
     */
    public function __construct() {
        // Add the license management submenu under the plugin's main menu.
        add_action( 'admin_menu', [ $this, 'register_license_menu' ] );

        // Register settings for the license key.
        add_action( 'admin_init', [ $this, 'register_license_settings' ] );

        // Handle the license activation form submission.
        add_action( 'admin_post_dhwp_activate_license', [ $this, 'process_license_activation' ] );

        // Hook into license key updates to trigger additional actions (e.g., logging).
        add_action( 'update_option_dhwp_license_key', function ( $old_value, $new_value ) {
            // Log the updated license key for debugging or audit purposes.
            // error_log( 'License key updated to: ' . $new_value );
        }, 10, 2 );
    }

    /**
     * Registers the license management submenu under the plugin's main menu.
     */
    public function register_license_menu() {
        add_submenu_page(
            'dhwp-starter-plugin/dhwp-starter-plugin.php', // Parent menu slug.
            __( 'Plugin License', 'dhwp-starter-plugin' ), // Page title.
            __( 'Plugin License', 'dhwp-starter-plugin' ), // Menu title.
            'manage_options',                              // Required capability.
            'dhwp-plugin-license',                         // Menu slug.
            [ $this, 'render_license_page' ]               // Callback to render the page.
        );
    }

    /**
     * Registers the license key setting for the WordPress settings API.
     */
    public function register_license_settings() {
        register_setting(
            'dhwp_license_group', // Option group.
            'dhwp_license_key',   // Option name.
            [ 'sanitize_callback' => 'sanitize_text_field' ] // Sanitize input.
        );
    }

    /**
     * Renders the HTML for the license management page.
     */
    public function render_license_page() {
        $license_key = esc_attr( get_option( 'dhwp_license_key', '' ) );
        $activation_status = get_option( 'dhwp_license_activation_status', '' );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Plugin License Activation', 'dhwp-starter-plugin' ); ?></h1>
            
            <!-- License Key Form -->
            <form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>">
                <?php
                settings_fields( 'dhwp_license_group' );
                do_settings_sections( 'dhwp_license_group' );
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e( 'License Key', 'dhwp-starter-plugin' ); ?></th>
                        <td>
                            <input type="text" name="dhwp_license_key" value="<?php echo esc_attr( $license_key ); ?>" size="40" />
                        </td>
                    </tr>
                </table>
                <?php submit_button( __( 'Save License Key', 'dhwp-starter-plugin' ) ); ?>
            </form>

            <!-- License Activation Form -->
            <?php if ( $license_key ) : ?>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <input type="hidden" name="action" value="dhwp_activate_license" />
                    <?php wp_nonce_field( 'dhwp_activate_license_nonce', 'dhwp_activate_license_nonce_field' ); ?>
                    <input type="hidden" name="dhwp_license_key" value="<?php echo esc_attr( $license_key ); ?>" />
                    <?php submit_button( __( 'Activate License', 'dhwp-starter-plugin' ), 'secondary' ); ?>
                </form>
            <?php endif; ?>

            <!-- License Status Display -->
            <?php if ( $license_key ) : ?>
                <?php
                $is_valid = $this->validate_license_key( $license_key );
                $status_message = $is_valid
                    ? __( 'License is valid.', 'dhwp-starter-plugin' )
                    : __( 'License is invalid.', 'dhwp-starter-plugin' );
                $status_color = $is_valid ? 'green' : 'red';
                ?>
                <p style="color: <?php echo esc_attr( $status_color ); ?>;">
                    <?php echo esc_html( $status_message ); ?>
                </p>
            <?php endif; ?>

            <!-- Activation Status Feedback -->
            <?php if ( $activation_status ) : ?>
                <?php
                $status_message = $activation_status === 'success'
                    ? __( 'License activated successfully.', 'dhwp-starter-plugin' )
                    : sprintf(
                        __( 'License activation failed: %s', 'dhwp-starter-plugin' ),
                        esc_html( $activation_status )
                    );
                $status_color = $activation_status === 'success' ? 'green' : 'red';
                ?>
                <p style="color: <?php echo esc_attr( $status_color ); ?>;">
                    <?php echo esc_html( $status_message ); ?>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Handles the license activation form submission.
     *
     * Verifies user permissions, nonce, and processes the license activation.
     */
    public function process_license_activation() {
        // Check if the user has the required permissions.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have permission to perform this action.', 'dhwp-starter-plugin' ) );
        }

        // Verify the nonce for security.
        check_admin_referer( 'dhwp_activate_license_nonce', 'dhwp_activate_license_nonce_field' );

        // Sanitize and retrieve the license key from the form.
        $license_key = isset( $_POST['dhwp_license_key'] )
            ? sanitize_text_field( wp_unslash( $_POST['dhwp_license_key'] ) )
            : '';

        // Activate the license and update the activation status.
        $result = $this->activate_license_key( $license_key );
        update_option(
            'dhwp_license_activation_status',
            $result === true ? 'success' : ( $result ? $result : __( 'Unknown error', 'dhwp-starter-plugin' ) )
        );

        // Redirect back to the license page.
        wp_safe_redirect( admin_url( 'options-general.php?page=dhwp-plugin-license' ) );
        exit;
    }

    /**
     * Activates a license key via the WooCommerce API.
     *
     * @param string $license_key The license key to activate.
     * @return bool|string True if successful, error message otherwise.
     */
    public function activate_license_key( $license_key ) {
        $site_url = home_url();
        $api_url  = 'https://store.digitalhill.com/wp-json/lmfwc/v2/licenses/activate/' . urlencode( $license_key ) . '?instance=' . urlencode( $site_url );

        $args = [
            'timeout' => 15,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode( $this->api_consumer_key . ':' . $this->api_consumer_secret ),
            ],
        ];

        $response = wp_remote_get( $api_url, $args );
        if ( is_wp_error( $response ) ) {
            return $response->get_error_message();
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        return isset( $data['success'] ) && $data['success'] === true
            ? true
            : ( isset( $data['message'] ) ? $data['message'] : false );
    }

    /**
     * Validates a license key via the WooCommerce API.
     *
     * @param string $license_key The license key to validate.
     * @return bool True if the license is valid, false otherwise.
     */
    public function validate_license_key( $license_key ) {
        $api_url = 'https://store.digitalhill.com/wp-json/lmfwc/v2/licenses/validate?license_key=' . urlencode( $license_key );
        $args    = [
            'timeout' => 15,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode( $this->api_consumer_key . ':' . $this->api_consumer_secret ),
            ],
        ];

        $response = wp_remote_get( $api_url, $args );
        if ( is_wp_error( $response ) ) {
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        return isset( $data['success'] ) && $data['success'] === true;
    }
}