<?php

namespace ProfilePress\Core\Membership\DigitalProducts;

use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;

class DownloadHandler
{
    public function __construct()
    {
        add_action('init', [$this, 'process_download'], 100);
    }

    /**
     * Used to process a signed URL for processing downloads
     *
     * @return array|false
     */
    private function process_signed_download_url()
    {
        $parts = parse_url(add_query_arg(array()));
        wp_parse_str($parts['query'], $query_args);
        $url = add_query_arg($query_args, site_url());

        $valid_token = DownloadService::init()->validate_url_token($url);

        if ( ! $valid_token) return false;

        $order_parts = explode(':', rawurldecode($_GET['ppress_file']));
        $order_id    = isset($order_parts[0]) ? (int)$order_parts[0] : null;
        $plan_id     = isset($order_parts[1]) ? (int)$order_parts[1] : null;
        $file_index  = isset($order_parts[2]) ? (int)$order_parts[2] : null;

        $downloads = ppress_get_plan($plan_id)->get_downloads();

        $order = OrderFactory::fromId($order_id);

        $file_url = '';

        if (isset($downloads['files']) && is_array($downloads['files'])) {
            $file_url = key(array_slice($downloads['files'], intval($file_index), 1));
        }

        $has_access = false;

        if ($this->is_admin_initiated_downloads($order_id, $file_url)) {
            $has_access = true;
        } else {

            // Check to make sure not at download limit
            if (DownloadService::init()->is_file_at_download_limit($plan_id, $order_id, $file_url)) {
                wp_die(apply_filters('ppress_download_limit_reached_text', __('Sorry but you have hit your download limit for this file.', 'wp-user-avatar')), __('Error', 'wp-user-avatar'), array('response' => 403));
            }

            if (
                apply_filters('ppress_file_downloads_subscription_check', true) &&
                ! CustomerFactory::fromId($order->customer_id)->has_active_subscription($plan_id)
            ) {
                wp_die(
                    sprintf(
                        __('You must have an active subscription to %s in order to download this file.', 'wp-user-avatar'),
                        ppress_get_plan($plan_id)->get_name()
                    ),
                    __('Access Denied', 'wp-user-avatar')
                );
            }
        }

        if ($plan_id == $order->plan_id && $order->is_completed()) {

            $get_downloads = ppress_get_plan($plan_id)->get_downloads();

            if (
                isset($get_downloads['files']) &&
                ! is_null($file_index) &&
                ! empty(array_slice($get_downloads['files'], intval($file_index), 1))
            ) {
                $has_access = true;
            }
        }

        return [
            'has_access'     => $has_access,
            'order_id'       => $order_id,
            'plan_id'        => $plan_id,
            'file_index'     => $file_index,
            'requested_file' => $file_url
        ];
    }

    private function get_download_content_type($file_path)
    {
        $file_extension = strtolower(substr(strrchr($file_path, '.'), 1));
        $ctype          = 'application/force-download';

        foreach (get_allowed_mime_types() as $mime => $type) {
            $mimes = explode('|', $mime);
            if (in_array($file_extension, $mimes, true)) {
                $ctype = $type;
                break;
            }
        }

        return $ctype;
    }

    /**
     * Determines if a file should be allowed to be downloaded by making sure it's within the wp-content directory.
     *
     * @param $requested_file
     *
     * @return boolean
     */
    private function local_file_location_is_allowed($requested_file)
    {
        $file_details = parse_url($requested_file);

        $should_allow = true;

        // If the file is an absolute path, make sure it's in the wp-content directory, to prevent store owners from accidentally allowing privileged files from being downloaded.
        if (( ! isset($file_details['scheme']) || ! in_array($file_details['scheme'], ['http', 'https'])) && isset($file_details['path'])) {

            /** This is an absolute path */
            $requested_file         = wp_normalize_path(realpath($requested_file));
            $normalized_abspath     = wp_normalize_path(ABSPATH);
            $normalized_content_dir = wp_normalize_path(WP_CONTENT_DIR);

            if (0 !== strpos($requested_file, $normalized_abspath) || false === strpos($requested_file, $normalized_content_dir)) {
                // If the file is not within the WP_CONTENT_DIR, it should not be able to be downloaded.
                $should_allow = false;
            }

        }

        return $should_allow;
    }

    /**
     * Determine if the file being requested is hosted locally or not
     *
     * @param string $requested_file The file being requested
     *
     * @return bool                   If the file is hosted locally or not
     */
    private function is_local_file($requested_file)
    {
        $site_url       = preg_replace('#^https?://#', '', site_url());
        $requested_file = preg_replace('#^(https?|file)://#', '', $requested_file);

        $is_local_url  = strpos($requested_file, $site_url) === 0;
        $is_local_path = strpos($requested_file, '/') === 0;

        return ($is_local_url || $is_local_path);
    }

    /**
     * Reads file in chunks so big downloads are possible without changing PHP.INI
     *
     * See https://github.com/bcit-ci/CodeIgniter/wiki/Download-helper-for-large-files
     *
     * @param string $file The file
     * @param boolean $retbytes Return the bytes of file
     *
     * @return   bool|string        If string, $status || $cnt
     */
    private function readfile_chunked($file, $retbytes = true)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        ob_start();

        // If output buffers exist, make sure they are closed.
        if (ob_get_length()) ob_clean();

        $chunksize = 1024 * 1024;
        $cnt       = 0;
        $handle    = @fopen($file, 'r');

        if ($size = @filesize($file)) {
            header("Content-Length: " . $size);
        }

        if (false === $handle) {
            return false;
        }

        if (isset($_SERVER['HTTP_RANGE'])) {
            list($size_unit, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            if ('bytes' === $size_unit) {
                if (strpos(',', $range)) {
                    list($range) = explode(',', $range, 1);
                }
            } else {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                exit;
            }
        } else {
            $range = '';
        }

        if (empty($range)) {
            $seek_start = null;
            $seek_end   = null;
        } else {
            list($seek_start, $seek_end) = explode('-', $range, 2);
        }

        $seek_end   = (empty($seek_end)) ? ($size - 1) : min(abs(intval($seek_end)), ($size - 1));
        $seek_start = (empty($seek_start) || $seek_end < abs(intval($seek_start))) ? 0 : max(abs(intval($seek_start)), 0);

        // Only send partial content header if downloading a piece of the file (IE workaround)
        if ($seek_start > 0 || $seek_end < ($size - 1)) {
            header(sprintf('%s 206 Partial Content', ppress_var($_SERVER, 'SERVER_PROTOCOL', 'HTTP/1.1', true)));
            header('Content-Range: bytes ' . $seek_start . '-' . $seek_end . '/' . $size);
            header('Content-Length: ' . ($seek_end - $seek_start + 1));
        } else {
            header("Content-Length: $size");
        }

        header('Accept-Ranges: bytes');

        ppress_set_time_limit(false);

        fseek($handle, $seek_start);

        while ( ! @feof($handle)) {
            $buffer = @fread($handle, $chunksize);
            echo $buffer;
            ob_flush();

            if (ob_get_length()) {
                ob_flush();
                flush();
            }

            if ($retbytes) {
                $cnt += strlen($buffer);
            }

            if (connection_status() != 0) {
                @fclose($handle);
                exit;
            }
        }

        ob_flush();

        $status = @fclose($handle);

        if ($retbytes && $status) return $cnt;

        return $status;
    }

    /**
     * Deliver the download file
     *
     * If enabled, the file is symlinked to better support large file downloads
     *
     * @param string $file
     * @param bool $redirect True if we should perform a header redirect
     *
     * @return   void
     */
    private function deliver_download($file = '', $redirect = false)
    {
        if ($redirect) {
            header('Location: ' . $file);
        } else {
            $this->readfile_chunked($file);
        }
    }

    /**
     *
     * Important so admins can always download files
     *
     * @param $order_id
     * @param $file_url
     *
     * @return bool
     */
    private function is_admin_initiated_downloads($order_id, $file_url = '')
    {
        if (is_user_logged_in() && current_user_can('manage_options')) {

            $order = OrderFactory::fromId($order_id);

            $user_id = $order->get_customer()->get_user_id();

            // order was made by admin, log the download
            if ($user_id > 0 && get_current_user_id() === $user_id) {
                if ( ! empty($file_url)) {
                    DownloadService::init()->add_download_log([
                        'plan_id'  => $order->get_plan_id(),
                        'file_url' => $file_url,
                        'order_id' => $order_id
                    ]);
                }
            }

            return true;
        }

        return false;
    }

    public function process_download()
    {
        if (empty($_GET['ppress_file']) || empty($_GET['ttl']) || empty($_GET['token'])) {
            return;
        }

        if ('true' === ppress_get_file_downloads_setting('access_restriction') && ! is_user_logged_in()) {
            wp_die(__('You must be logged in to download files.', 'wp-user-avatar') . ' <a href="' . esc_url(wp_login_url(ppress_get_current_url_query_string())) . '">' . __('Login', 'wp-user-avatar') . '</a>', __('Log in to Download Files', 'wp-user-avatar'), 403);
        }

        $args = $this->process_signed_download_url();

        if ( ! $args['has_access']) {
            $error_message = __('You do not have permission to download this file', 'wp-user-avatar');
            wp_die(apply_filters('ppress_deny_download_message', $error_message, __('Order Verification Failed', 'wp-user-avatar')), __('Error', 'wp-user-avatar'), array('response' => 403));
        }

        $method = $raw_method = ppress_get_file_downloads_setting('download_method', 'direct', true);

        $requested_file = $args['requested_file'];

        $file_details = parse_url($requested_file);
        $schemes      = ['http', 'https']; // Direct URL schemes

        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' && isset($file_details['scheme']) && ! in_array($file_details['scheme'], stream_get_wrappers())) {
            wp_die(__('Error 103: Error downloading file. Please contact support.', 'wp-user-avatar'), __('File download error', 'wp-user-avatar'), 501);
        }

        if (
            ( ! isset($file_details['scheme']) || ! in_array($file_details['scheme'], $schemes)) &&
            isset($file_details['path']) && file_exists($requested_file)
        ) {
            /**
             * Download method is set to Redirect in settings but an absolute path was provided
             * We need to switch to a direct download in order for the file to download properly
             */
            $method = 'direct';
        }

        $file_is_in_allowed_location = $this->local_file_location_is_allowed($requested_file);

        if (false === $file_is_in_allowed_location) {
            wp_die(__('Sorry, this file could not be downloaded.', 'wp-user-avatar'), __('Error Downloading File', 'wp-user-avatar'), 403);
        }

        if ( ! $this->is_admin_initiated_downloads($args['order_id'])) {
            DownloadService::init()->add_download_log([
                'plan_id'  => $args['plan_id'],
                'file_url' => $requested_file,
                'order_id' => $args['order_id']
            ]);
        }

        ppress_set_time_limit(false);

        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1);
        }
        @ini_set('zlib.output_compression', 'Off');
        @session_write_close();

        nocache_headers();
        header('Robots: none');
        header('X-Robots-Tag: noindex, nofollow', true);
        header('Content-Type: ' . $this->get_download_content_type($requested_file));
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename="' . basename($requested_file) . '"');
        header('Content-Transfer-Encoding: binary');

        // If the file isn't locally hosted, process the redirect
        if (filter_var($requested_file, FILTER_VALIDATE_URL) && ! $this->is_local_file($requested_file)) {
            $this->deliver_download($requested_file, true);
            exit;
        }

        if ($method == 'redirect') {
            // Redirect straight to the file
            $this->deliver_download($requested_file, true);
            exit;
        }

        $direct    = false;
        $file_path = $requested_file;

        if (( ! isset($file_details['scheme']) || ! in_array($file_details['scheme'], $schemes)) && isset($file_details['path']) && file_exists($requested_file)) {
            /** This is an absolute path */
            $direct    = true;
            $file_path = $requested_file;
        } elseif (defined('UPLOADS') && strpos($requested_file, UPLOADS) !== false) {
            /**
             * This is a local file given by URL so we need to figure out the path
             * UPLOADS is always relative to ABSPATH
             * site_url() is the URL to where WordPress is installed
             */
            $file_path = str_replace(site_url(), '', $requested_file);
            $file_path = realpath(ABSPATH . $file_path);
            $direct    = true;
        } elseif (strpos($requested_file, content_url()) !== false) {
            /** This is a local file given by URL so we need to figure out the path */
            $file_path = str_replace(content_url(), WP_CONTENT_DIR, $requested_file);
            $file_path = realpath($file_path);
            $direct    = true;
        } elseif (strpos($requested_file, set_url_scheme(content_url(), 'https')) !== false) {
            /** This is a local file given by an HTTPS URL so we need to figure out the path */
            $file_path = str_replace(set_url_scheme(content_url(), 'https'), WP_CONTENT_DIR, $requested_file);
            $file_path = realpath($file_path);
            $direct    = true;
        }

        // Set the file size header
        header("Content-Length: " . @filesize($file_path));

        $server_software = getenv('SERVER_SOFTWARE');

        if ($raw_method == 'xsendfile') {

            if (
                function_exists('apache_get_modules') && in_array('mod_xsendfile', apache_get_modules(), true) ||
                stristr($server_software, 'apache')
            ) {
                // https://tn123.org/mod_xsendfile/
                header('X-Sendfile: ' . $file_path);
                exit;
            }

            if (stristr($server_software, 'litespeed')) {
                // We need a path relative to the domain
                $file_path = trim(str_ireplace(realpath($_SERVER['DOCUMENT_ROOT']), '', $file_path), '/');
                // https://www.litespeedtech.com/support/wiki/doku.php/litespeed_wiki:config:internal-redirect
                header("X-LiteSpeed-Location: /$file_path");
                exit;
            }

            if (stristr($server_software, 'lighttpd')) {
                // apparently, X-Sendfile also work as X-LIGHTTPD-send-file is an alias that has been deprecated though still work
                // see https://redmine.lighttpd.net/projects/lighttpd/wiki/Docs_ModFastCGI#X-Sendfile
                header("X-LIGHTTPD-send-file: $file_path");
                exit;
            }

            if ($direct && (stristr($server_software, 'nginx') || stristr($server_software, 'cherokee'))) {
                // We need a path relative to the domain
                $file_path = trim(str_ireplace(realpath($_SERVER['DOCUMENT_ROOT']), '', $file_path), '/');
                // https://www.nginx.com/resources/wiki/start/topics/examples/xsendfile/
                // https://cherokee-project.com/doc/other_goodies.html#x-sendfile
                header("X-Accel-Redirect: /$file_path");
                exit;
            }

            ppress_log_error('X-Sendfile file download method is not working.');
        }

        if ($direct) {
            $this->deliver_download($file_path);
        } else {

            // The file supplied does not have a discoverable absolute path
            $this->deliver_download($requested_file, true);
        }

        exit;
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