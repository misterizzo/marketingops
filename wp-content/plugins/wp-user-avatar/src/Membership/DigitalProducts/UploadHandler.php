<?php

namespace ProfilePress\Core\Membership\DigitalProducts;

use ProfilePressVendor\PAnD;

class UploadHandler
{
    public function __construct()
    {
        add_filter('upload_dir', [$this, 'upload_dir']);
        add_filter('wp_unique_filename', [$this, 'update_filename'], 10, 3);

        add_action('admin_init', [$this, 'create_protection_files']);

        add_action('ppress_admin_notices', [$this, 'admin_notice']);

        add_filter('upload_mimes', [$this, 'allowed_mime_types'], 999);

        add_filter('file_is_displayable_image', [$this, 'file_is_displayable_image'], 99, 2);
    }

    /**
     * Stop WordPress from creating different image sizes.
     *
     * @param $result
     * @param $path
     *
     * @return bool
     */
    public function file_is_displayable_image($result, $path)
    {
        if (strpos($path, ppress_var(wp_upload_dir(), 'basedir') . '/ppress_uploads') !== false) {
            $result = false;
        }

        return $result;
    }

    public function allowed_mime_types($existing_mimes)
    {
        $existing_mimes['zip']             = 'application/zip';
        $existing_mimes['epub']            = 'application/epub+zip';
        $existing_mimes['mobi']            = 'application/x-mobipocket-ebook';
        $existing_mimes['m4r']             = 'audio/aac';
        $existing_mimes['aif']             = 'audio/x-aiff';
        $existing_mimes['aiff']            = 'audio/aiff';
        $existing_mimes['psd']             = 'image/photoshop';
        $existing_mimes['exe']             = 'application/octet-stream';
        $existing_mimes['apk']             = 'application/vnd.android.package-archive';
        $existing_mimes['msi']             = 'application/x-ole-storage';
        $existing_mimes['csv']             = 'text/csv';
        $existing_mimes['doc']             = 'application/msword';
        $existing_mimes["pot|pps|ppt"]     = "application/vnd.ms-powerpoint";
        $existing_mimes["xla|xls|xlt|xlw"] = "application/vnd.ms-excel";
        $existing_mimes["docx"]            = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
        $existing_mimes["pptx"]            = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
        $existing_mimes["odt"]             = "application/vnd.oasis.opendocument.text";
        $existing_mimes["odp"]             = "application/vnd.oasis.opendocument.presentation";
        $existing_mimes["ods"]             = "application/vnd.oasis.opendocument.spreadsheet";

        return $existing_mimes;
    }

    public function create_protection_files($force = false, $method = false)
    {
        if (false === get_transient('ppress_check_protection_files') || $force) {

            $upload_path = $this->get_upload_dir();

            // Top level .htaccess file
            $rules = 'redirect' === $method ? 'Options -Indexes' : 'deny from all';

            if (file_exists($upload_path . '/.htaccess')) {
                $contents = @file_get_contents($upload_path . '/.htaccess');
                if ($contents !== $rules || ! $contents) {
                    // Update the .htaccess rules if they don't match
                    @file_put_contents($upload_path . '/.htaccess', $rules);
                }
            } elseif (wp_is_writable($upload_path)) {
                // Create the file if it doesn't exist
                @file_put_contents($upload_path . '/.htaccess', $rules);
            }

            // Top level blank index.php
            if ( ! file_exists($upload_path . '/index.php') && wp_is_writable($upload_path)) {
                @file_put_contents($upload_path . '/index.php', '<?php' . PHP_EOL . '// Silence is golden.');
            }

            // Now place index.php files in all sub folders
            $folders = $this->scan_folders($upload_path);

            foreach ($folders as $folder) {
                // Create index.php, if it doesn't exist
                if ( ! file_exists($folder . 'index.php') && wp_is_writable($folder)) {
                    @file_put_contents($folder . 'index.php', '<?php' . PHP_EOL . '// Silence is golden.');
                }
            }

            // Check for the files once per day
            set_transient('ppress_check_protection_files', true, DAY_IN_SECONDS);
        }
    }

    /**
     * Change upload dir for downloadable files.
     *
     * @param array $pathdata Array of paths.
     *
     * @return array
     */
    public function upload_dir($pathdata)
    {
        if (isset($_POST['type']) && 'ppress_downloadable_plan' === $_POST['type']) {

            if (empty($pathdata['subdir'])) {
                $pathdata['path']   = $pathdata['path'] . '/ppress_uploads';
                $pathdata['url']    = $pathdata['url'] . '/ppress_uploads';
                $pathdata['subdir'] = '/ppress_uploads';
            } else {

                $new_subdir = '/ppress_uploads' . $pathdata['subdir'];

                $pathdata['path']   = str_replace($pathdata['subdir'], $new_subdir, $pathdata['path']);
                $pathdata['url']    = str_replace($pathdata['subdir'], $new_subdir, $pathdata['url']);
                $pathdata['subdir'] = str_replace($pathdata['subdir'], $new_subdir, $pathdata['subdir']);
            }
        }

        return $pathdata;
    }

    /**
     * Change filename for WooCommerce uploads and prepend unique chars for security.
     *
     * @param string $full_filename Original filename.
     * @param string $ext Extension of file.
     * @param string $dir Directory path.
     *
     * @return string New filename with unique hash.
     */
    public function update_filename($full_filename, $ext, $dir)
    {
        if ( ! isset($_POST['type']) || ! 'ppress_downloadable_plan' === $_POST['type']) {
            return $full_filename;
        }

        if ( ! strpos($dir, 'ppress_uploads')) {
            return $full_filename;
        }

        if ('true' !== ppress_get_file_downloads_setting('downloads_add_hash_filename')) {
            return $full_filename;
        }

        return $this->unique_filename($full_filename, $ext);
    }

    /**
     * Change filename to append random text.
     *
     * @param string $full_filename Original filename with extension.
     * @param string $ext Extension.
     *
     * @return string Modified filename.
     */
    public function unique_filename($full_filename, $ext)
    {
        $ideal_random_char_length = 6;   // Not going with a larger length because then downloaded filename will not be pretty.
        $max_filename_length      = 255; // Max file name length for most file systems.
        $length_to_prepend        = min($ideal_random_char_length, $max_filename_length - strlen($full_filename) - 1);

        if (1 > $length_to_prepend) {
            return $full_filename;
        }

        $suffix   = strtolower(wp_generate_password($length_to_prepend, false, false));
        $filename = $full_filename;

        if (strlen($ext) > 0) {
            $filename = substr($filename, 0, strlen($filename) - strlen($ext));
        }

        return str_replace(
            $filename,
            "$filename-$suffix",
            $full_filename
        );
    }

    public function get_upload_dir()
    {
        $wp_upload_dir = wp_upload_dir();
        $path          = $wp_upload_dir['basedir'] . '/' . 'ppress_uploads';

        wp_mkdir_p($path);

        return $path;
    }

    public function scan_folders($path = '', $return = array())
    {
        $path  = ($path === '') ? dirname(__FILE__) : $path;
        $lists = @scandir($path);

        // Bail early if nothing to scan
        if (empty($lists)) {
            return $return;
        }

        // Loop through directory items
        foreach ($lists as $f) {
            $dir = $path . DIRECTORY_SEPARATOR . $f;

            // Skip if not a directory
            if ( ! is_dir($dir) || ($f === ".") || ($f === "..")) {
                continue;
            }

            // Maybe add directory to return array
            if ( ! in_array($dir, $return, true)) {
                $return[] = trailingslashit($dir);
            }

            // Recursively scan
            $this->scan_folders($dir, $return);
        }

        return $return;
    }

    /**
     * Check if uploads directory is protected.
     *
     * @return bool
     */
    protected function is_uploads_directory_protected()
    {
        $cache_key = '_ppress_upload_directory_status';
        $status    = get_transient($cache_key);

        // Check for cache.
        if (false !== $status) {
            return 'protected' === $status;
        }

        // Get only data from the uploads directory.
        $uploads = wp_get_upload_dir();

        // Check for the "uploads/ppress_uploads" directory.
        $response = wp_safe_remote_get(
            esc_url_raw($uploads['baseurl'] . '/ppress_uploads/'),
            ['redirection' => 0]
        );

        $response_code    = intval(wp_remote_retrieve_response_code($response));
        $response_content = wp_remote_retrieve_body($response);

        // Check if returns 200 with empty content in case can open an index.html file,
        // and check for non-200 codes in case the directory is protected.
        $is_protected = (200 === $response_code && empty($response_content)) || (200 !== $response_code);
        set_transient($cache_key, $is_protected ? 'protected' : 'unprotected', 1 * DAY_IN_SECONDS);

        return $is_protected;
    }

    /**
     * Notice about uploads directory begin unprotected.
     */
    public function admin_notice()
    {
        if ( ! ppress_is_any_active_plan() || ! ppress_is_any_enabled_payment_method()) {
            return;
        }

        if ( ! PAnD::is_admin_notice_active('ppress_dismissed_uploads_directory_is_unprotected-forever')) return;

        if ($this->is_uploads_directory_protected()) return;

        $uploads = wp_get_upload_dir();

        $notice = wp_kses_post(
            sprintf(
            /* translators: 1: uploads directory URL 2: documentation URL */
                __('Your store\'s uploads directory is <a href="%1$s">browsable via the web</a>. We strongly recommend <a href="%2$s">configuring your web server to prevent directory indexing</a>.', 'wp-user-avatar'),
                esc_url($uploads['baseurl'] . '/ppress_uploads'),
                'https://profilepress.com/article/sell-downloads-wordpress-membership/#protecting-uploads-directory'
            )
        );

        echo '<div data-dismissible="ppress_dismissed_uploads_directory_is_unprotected-forever" class="notice notice-warning is-dismissible">';
        echo "<p>$notice</p>";
        echo '</div>';
    }

    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}