<?php
/**
 * REST API settings
 *
 * @package Theme_name
 * @since 1.0.0
 */

remove_action( 'wp_head', 'rest_output_link_wp_head' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'template_redirect', 'rest_output_link_header', 11 );
remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd', 10 );

add_filter( 'rest_endpoints', 'adem_clear_rest_endpoints' );
/**
 * Filters REST API endpoints to allow only specific custom namespaces.
 *
 * This function removes all default WordPress REST API routes (e.g., /wp/v2/*, /oembed/*)
 * and returns only the endpoints that match the allowed namespaces.
 * Useful when you want to expose only your own custom REST API routes
 * and hide all built-in endpoints from public access.
 *
 * @param array $endpoints {
 *     Registered REST API endpoints.
 *
 *     @type string $route   The REST route (e.g., '/myapi/v1/example').
 *     @type array  $details Details about the endpoint, such as methods, callback, and permission checks.
 * }
 *
 * @return array Filtered list of endpoints containing only the allowed namespaces.
 */
function adem_clear_rest_endpoints( $endpoints ) {
	$allowed_namespaces = array(
		'/file-monitor/v1',
	);

	$allowed = array();

	foreach ( $allowed_namespaces as $ns ) {
		foreach ( $endpoints as $route => $details ) {
			if ( strpos( $route, $ns ) === 0 ) {
				$allowed[ $route ] = $details;
			}
		}
	}

	return $allowed;
}
