<?php
class DHWP_License_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_license_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_license_menu() {
        add_options_page(
            'Plugin License',
            'Plugin License',
            'manage_options',
            'dhwp-plugin-license',
            [$this, 'license_page_html']
        );
    }

    public function register_settings() {
        register_setting('dhwp_license_group', 'dhwp_license_key');
    }

    public function license_page_html() {
        ?>
        <div class="wrap">
            <h1>Plugin License Activation</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('dhwp_license_group');
                do_settings_sections('dhwp_license_group');
                $license_key = esc_attr(get_option('dhwp_license_key'));
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">License Key</th>
                        <td>
                            <input type="text" name="dhwp_license_key" value="<?php echo $license_key; ?>" size="40" />
                        </td>
                    </tr>
                </table>
                <?php submit_button('Save License Key'); ?>
            </form>
            <?php
            if ($license_key) {
                $status = DHWP_License_Admin::verify_license($license_key);
                if ($status === true) {
                    echo '<p style="color:green;">License is valid.</p>';
                } else {
                    echo '<p style="color:red;">License is invalid.</p>';
                }
            }
            ?>
        </div>
        <?php
    }

    public static function verify_license($license_key) {
        // Replace with your License Manager API endpoint and logic
        $api_url = 'https://your-license-manager.com/api/verify?license_key=' . urlencode($license_key);
        $response = wp_remote_get($api_url);
        if (is_wp_error($response)) {
            return false;
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        return isset($data['success']) && $data['success'] === true;
    }
}
