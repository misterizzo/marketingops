<?php

namespace ProfilePress\Libsodium;

class RequirementChecker
{
    /**
     * Plugin display name
     * @var string
     */
    protected $plugin_name;

    /**
     * Array of checks
     * @var array
     */
    protected $checks;

    /**
     * Array of check methods
     * @var array
     */
    private $check_methods;

    /**
     * Array of errors
     * @var array
     */
    protected $errors = array();

    /**
     * Class constructor
     *
     * @param string $plugin_name plugin display name
     * @param array $to_check checks to perform
     */
    public function __construct($plugin_name = '', $to_check = array())
    {
        $this->checks      = $to_check;
        $this->plugin_name = $plugin_name;

        // Add default checks
        $this->add_check('php', array($this, 'check_php'));
        $this->add_check('php_extensions', array($this, 'check_php_extensions'));
        $this->add_check('wp', array($this, 'check_wp'));
        $this->add_check('plugins', array($this, 'check_plugins'));
    }

    /**
     * Adds the new check
     *
     * @param string $check_name name of the check
     * @param mixed $callback callable string or array
     *
     * @return $this
     */
    public function add_check($check_name, $callback)
    {
        $this->check_methods[$check_name] = $callback;

        return $this;

    }

    /**
     * Runs checks
     * @return $this
     */
    public function check()
    {
        foreach ($this->checks as $thing_to_check => $comparsion) {

            if (isset($this->check_methods[$thing_to_check]) && is_callable($this->check_methods[$thing_to_check])) {
                call_user_func($this->check_methods[$thing_to_check], $comparsion, $this);
            }

        }

        return $this;
    }

    /**
     * Adds the error
     * @return $this
     */
    public function add_error($error_message)
    {
        $this->errors[] = $error_message;

        return $this;

    }

    /**
     * Check if requirements has been satisfied
     * @return boolean
     */
    public function satisfied()
    {
        $this->check();

        return empty($this->errors);
    }

    /**
     * Displays notice for user about the plugin requirements
     *
     * @return void
     */
    public function notice()
    {
        $install_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=wp-user-avatar'), 'install-plugin_wp-user-avatar');

        $activate_url = wp_nonce_url(admin_url('plugins.php?action=activate&plugin=wp-user-avatar%2Fwp-user-avatar.php'), 'activate-plugin_wp-user-avatar/wp-user-avatar.php');

        echo '<div class="error">';

        foreach ($this->errors as $error) {
            echo '<p>' . str_replace(['{ppressinstallurl}', '{ppressactivateurl}'], [$install_url, $activate_url], $error) . '</p>';
        }

        echo '</div>';

    }

    /**
     * Default check methods
     */

    /**
     * Check PHP version
     *
     * @param string $version version needed
     * @param object $requirements requirements class
     *
     * @return void
     */
    public function check_php($version, $requirements)
    {
        if (version_compare(phpversion(), $version, '<')) {
            $requirements->add_error(sprintf(__('Minimum required version of PHP is %s. Your version is %s', 'profilepress-pro'), $version, phpversion()));
        }
    }

    /**
     * Check PHP extensions
     *
     * @param string $extensions array of extension names
     * @param object $requirements requirements class
     *
     * @return void
     */
    public function check_php_extensions($extensions, $requirements)
    {
        $missing_extensions = array();

        foreach ($extensions as $extension) {
            if ( ! extension_loaded($extension)) {
                $missing_extensions[] = $extension;
            }
        }

        if ( ! empty($missing_extensions)) {
            $requirements->add_error(sprintf(
                _n('PHP extension: %s', 'PHP extensions: %s', count($missing_extensions)),
                implode(', ', $missing_extensions),
                'profilepress-pro'
            ));
        }

    }

    /**
     * Check WordPress version
     *
     * @param string $version version needed
     * @param object $requirements requirements class
     *
     * @return void
     */
    public function check_wp($version, $requirements)
    {
        if (version_compare(get_bloginfo('version'), $version, '<')) {
            $requirements->add_error(sprintf(__('Minimum required version of WordPress is %s. Your version is %s', 'profilepress-pro'), $version, get_bloginfo('version')));
        }

    }

    public function is_plugin_installed($plugin_file)
    {
        if ( ! function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $installed_plugins = get_plugins();

        return isset($installed_plugins[$plugin_file]);
    }

    /**
     * Check if plugins are active and are in needed versions
     *
     * @param array $plugins array with plugins,
     *                              where key is the plugin file and value is the version
     * @param object $requirements requirements class
     *
     * @return void
     */
    public function check_plugins($plugins, $requirements)
    {
        $active_plugins_raw = wp_get_active_and_valid_plugins();

        if (is_multisite()) {
            $active_plugins_raw = array_merge($active_plugins_raw, wp_get_active_network_plugins());
        }

        $active_plugins          = array();
        $active_plugins_versions = array();

        foreach ($active_plugins_raw as $plugin_full_path) {
            $plugin_file                           = str_replace(WP_PLUGIN_DIR . '/', '', $plugin_full_path);
            $active_plugins[]                      = $plugin_file;
            $plugin_api_data                       = @get_file_data($plugin_full_path, array('Version'));
            $active_plugins_versions[$plugin_file] = $plugin_api_data[0];
        }

        foreach ($plugins as $plugin_file => $plugin_data) {

            $btn_text = esc_html__('Install ProfilePress Now', 'profilepress-pro');
            $url      = '{ppressinstallurl}';

            if ($this->is_plugin_installed($plugin_file)) {
                $btn_text = esc_html__('Activate ProfilePress Now', 'profilepress-pro');
                $url      = '{ppressactivateurl}';
            }

            if ( ! in_array($plugin_file, $active_plugins)) {

                $requirements->add_error(
                    sprintf(__('%s is not working because you need to install and activate the %s plugin. %s', 'profilepress-pro'),
                        $this->plugin_name,
                        $plugin_data['name'],
                        '</p><p><a href="' . $url . '" class="button-primary">' . $btn_text . '</a></p>'
                    )
                );

            } elseif (version_compare($active_plugins_versions[$plugin_file], $plugin_data['version'], '<')) {
                $requirements->add_error(sprintf(__('Minimum required version of %s plugin is %s. Your version is %s', 'profilepress-pro'), $plugin_data['name'], $plugin_data['version'], $active_plugins_versions[$plugin_file]));
            }

        }

    }
}