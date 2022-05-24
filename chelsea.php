<?php
/**
 * Chelsea is a versatile, custom WordPress bootloader using SHORTINIT.
 *
 * It is a robust drop-in file for your WordPress ABSPATH folder that (when
 * properly customized) allows you to conditionally load the bare minimum of PHP
 * files required to process a custom request while maintaining an ultra-small
 * memory footprint.
 *
 * Named after JJJ's favorite type of boot:
 * https://en.wikipedia.org/wiki/Chelsea_boot
 */

/**
 * Define required WordPress globals.
 *
 * @global WP       $wp           Current WordPress environment instance.
 * @global WP_Query $wp_query     WordPress Query object.
 * @global WP_Query $wp_the_query WordPress Query object.
 */
global $wp, $wp_query, $wp_the_query;

/**
 * Tell WordPress to only initialize its "short" version.
 */
if ( ! defined( 'SHORTINIT' ) ) {
	define( 'SHORTINIT', true );
}

/**
 * Load WordPress if not already (loads wp-config.php & wp-settings.php).
 */
if ( ! defined( 'ABSPATH' ) ) {
	require __DIR__ . '/wp-load.php';
}

/**
 * Load a bunch of really useful global PHP variables.
 */
if ( function_exists( 'is_admin' ) ) {
	require_once ABSPATH . WPINC . '/vars.php';
}

/** Request Overrides *********************************************************/

/**
 * By default, Chelsea assumes that you do not want for WordPress to go through
 * all of the normal work of parsing the request, because the major benefit of
 * a custom bootloader is that you probably already know what you are doing.
 *
 * To continue letting WordPress parse the request, delete these filters:
 */
add_filter( 'do_parse_request', '__return_false' );
add_filter( 'pre_handle_404',   '__return_true'  );

/** Optional Requirements *****************************************************/

/**
 * Edit this portion of this file to "require_once" any files that should be
 * part of your custom WordPress bootloader.
 **
 *    Warning: You are 100% responsible for the entire dependency tree, and this
 *             quickly spirals out of control into pure chaos if you do not test
 *             diligently and/or implement your own autoloading.
 *
 * Suggestion: Learn autoloading deeply <3
 */

/** Optional Query Arguments **************************************************/

/**
 * If you plan to use the default WordPress request parser, you will probably
 * want to "register" your custom query variables so that you can precisely
 * target the proper requests.
 *
 * These variables are obviously examples, and you would want to change them
 * to anything better that suits your needs.
 */
$extra_query_vars = array(
	'chelsea',
	'bootloader'
);

// Skip if emptied above (or just delete this block if it's not needed)
if ( ! empty( $extra_query_vars ) ) {

	/**
	 * Filter "query_vars" and add the extra vars to the existing public ones.
	 */
	add_filter( 'query_vars', function( $public_query_vars = array() ) use ( $extra_query_vars ) {

		// Merge the arrays
		$retval = array_merge( $public_query_vars, $extra_query_vars );

		// Return
		return $retval;
	} );
}

/** Unhook Defaults ***********************************************************/

/**
 * The hooks below are loaded by WordPress via default-filters.php, and are
 * fully capable of fatal-erroring your bootloader if you are not careful.
 *
 * Delete them if you need to, but only if you are really-really sure!
 */

/**
 * Unhook KSES init
 */
if ( ! function_exists( 'kses_init' ) ) {
	remove_action( 'init',             'kses_init' );
	remove_action( 'set_current_user', 'kses_init' );
}

/**
 * Maybe unhook REST API
 */
if ( ! function_exists( 'rest_cookie_collect_status' ) ) {

	// Init
	remove_action( 'init',          'rest_api_init' );
	remove_action( 'rest_api_init', 'rest_api_default_filters', 10, 1 );
	remove_action( 'rest_api_init', 'register_initial_settings', 10 );
	remove_action( 'rest_api_init', 'create_initial_rest_routes', 99 );
	remove_action( 'parse_request', 'rest_api_loaded' );

	// Auth
	remove_action( 'xmlrpc_rsd_apis',                            'rest_output_rsd' );
	remove_action( 'wp_head',                                    'rest_output_link_wp_head', 10, 0 );
	remove_action( 'template_redirect',                          'rest_output_link_header',  11, 0 );
	remove_action( 'auth_cookie_malformed',                      'rest_cookie_collect_status' );
	remove_action( 'auth_cookie_expired',                        'rest_cookie_collect_status' );
	remove_action( 'auth_cookie_bad_username',                   'rest_cookie_collect_status' );
	remove_action( 'auth_cookie_bad_hash',                       'rest_cookie_collect_status' );
	remove_action( 'auth_cookie_valid',                          'rest_cookie_collect_status' );
	remove_action( 'application_password_failed_authentication', 'rest_application_password_collect_status' );
	remove_action( 'application_password_did_authenticate',      'rest_application_password_collect_status', 10, 2 );
	remove_filter( 'rest_authentication_errors',                 'rest_application_password_check_errors',   90 );
	remove_filter( 'rest_authentication_errors',                 'rest_cookie_check_errors',                 100 );
}

/**
 * Maybe unhook Post Formats
 */
if ( ! function_exists( '_post_format_request' ) ) {
	remove_filter( 'request',             '_post_format_request' );
	remove_filter( 'term_link',           '_post_format_link', 10, 3 );
	remove_filter( 'get_post_format',     '_post_format_get_term' );
	remove_filter( 'get_terms',           '_post_format_get_terms', 10, 3 );
	remove_filter( 'wp_get_object_terms', '_post_format_wp_get_object_terms' );
}

/**
 * Maybe unhook closing comments for old posts
 */
if ( ! function_exists( '_close_comments_for_old_posts' ) ) {
	remove_filter( 'the_posts', '_close_comments_for_old_posts', 10, 2 );
}

/** User Files ****************************************************************/

/**
 * Needed for: wp_set_current_user() and related auth
 */
if ( ! function_exists( 'wp_set_current_user' ) ) {
	require_once ABSPATH . WPINC . '/pluggable.php';
	require_once ABSPATH . WPINC . '/user.php';
	require_once ABSPATH . WPINC . '/capabilities.php';
	require_once ABSPATH . WPINC . '/class-wp-query.php';
	require_once ABSPATH . WPINC . '/class-wp-role.php';
	require_once ABSPATH . WPINC . '/class-wp-roles.php';
	require_once ABSPATH . WPINC . '/class-wp-user.php';
	require_once ABSPATH . WPINC . '/class-wp-session-tokens.php';
	require_once ABSPATH . WPINC . '/class-wp-user-meta-session-tokens.php';
}

/** Init Constants ************************************************************/

/**
 * 'WP_PLUGIN_URL' and others are used by: wp_cookie_constants()
 *
 * @see WP->init();
 */
wp_plugin_directory_constants();

/**
 * 'ADMIN_COOKIE_PATH' and others are used by: wp_set_auth_cookie()
 *
 * @see WP->init();
 */
if ( is_multisite() ) {
	ms_cookie_constants();
}

/**
 * 'SECURE_AUTH_COOKIE' and others are used by: wp_parse_auth_cookie()
 *
 * @see WP->init();
 */
wp_cookie_constants();

/**
 * Sets: 'FORCE_SSL_ADMIN' and 'FORCE_SSL_LOGIN'
 *
 * @see WP->init();
 */
wp_ssl_constants();

/** Parse Request *************************************************************/

/**
 * Filters 'do_parse_request' and attempts to fix common fatal errors from
 * calling WP()->send_headers() & friends without including the necessary files.
 *
 * This big'ol thing only matters when you are trusting WordPress to parse the
 * request by deleting line 52 above. It's included here as an example of how
 * much of a pain it is to conditionally & manually load files on your own.
 *
 * @since 1.0.0
 * @param bool $doing
 * @return bool
 */
function chelsea_do_parse_request_fatal_error_fixer( $doing = true ) {
	global $wp_rewrite, $wp_query, $wp_the_query;

	// Bail early if already skipping request parsing
	if ( empty( $doing ) ) {
		return $doing;
	}

	// Setup The Query global
	if ( empty( $wp_the_query ) ) {
		require_once ABSPATH . WPINC . '/class-wp-post.php';
		$wp_the_query = new WP_Query();
	}

	// Copy The Query global
	if ( empty( $wp_query ) ) {
		$wp_query = $wp_the_query;
	}

	// Load rewrite rules
	if ( empty( $wp_rewrite ) ) {
		require_once ABSPATH . WPINC . '/rewrite.php';
		require_once ABSPATH . WPINC . '/class-wp-rewrite.php';

		$wp_rewrite = new WP_Rewrite();

		if ( ! function_exists( 'home_url' ) ) {
			require_once ABSPATH . WPINC . '/link-template.php';
		}
	}

	// Load query functions
	if ( ! function_exists( 'is_404' ) ) {
		require_once ABSPATH . WPINC . '/query.php';
	}

	// Load post functions
	if ( ! function_exists( 'get_post_types' ) ) {
		require_once ABSPATH . WPINC . '/post.php';
	}

	// Load taxonomy functions
	if ( ! function_exists( 'get_taxonomies' ) ) {
		require_once ABSPATH . WPINC . '/taxonomy.php';
	}

	// Load the feed functions
	if ( ! function_exists( 'get_default_feed' ) ) {
		require_once ABSPATH . WPINC . '/feed.php';
	}

	// Load the comment functions
	if ( ! function_exists( 'get_lastcommentmodified' ) ) {
		require_once ABSPATH . WPINC . '/comment.php';
	}

	// Options are oddly included in: functions.php, but...
	if ( ! function_exists( 'get_option' ) ) {
		require_once ABSPATH . WPINC . '/option.php';
	}

	// Return
	return $doing;
}
add_filter( 'do_parse_request', 'chelsea_do_parse_request_fatal_error_fixer' , 99, 3 );

/** Setup WordPress ***********************************************************/

/**
 * Below is, essentially, what every WordPress request does when it is trying
 * to determine what content to display in a browser, to an API endpoint, or
 * whatever else you may want to do with it.
 *
 * Because this is a custom bootloader, you are free to totally replace most of
 * this with anything if you want to, once you understand how all of it works.
 */

// Create a "wp" WordPress global object
$wp = new WP();

// Maybe set the current WordPress user
if ( function_exists( 'wp_set_current_user' ) ) {
	$wp->init();
}

/**
 * Here is where WordPress would normally parse the request into an array of
 * arguments that would be used to query the database.
 */
$wp->parsed = $wp->parse_request( $extra_query_vars );

// Send the basic headers
$wp->send_headers();

// Build the query string
$wp->build_query_string();

/**
 * Here is when WordPress would normally query the database for Posts from
 * inside of the `wp_posts` database table, using the $wp_the_query global,
 * which is a WP_Query object.
 */
if ( ! empty( $wp->parsed ) && is_callable( array( $wp_the_query, 'query' ) ) ) {
	$wp_the_query->query( $wp->query_vars );
}

/**
 * Here is when WordPress would normally attempt to handle a 404 situation.
 *
 * Some basic 404 handling is included below if you have chosen to override
 * it using the "pre_handle_404" filter above on line 53.
 */
if ( has_filter( 'pre_handle_404', '__return_true' ) ) {

	// Default status header code
	$wp->status_header = 404;

	/**
	 * When your custom conditions are met, if any, set the status_header value
	 * to 200.
	 */
	if ( true === true ) {
		$wp->status_header = 200;
	}

	/**
	 * You've decided that nothing could be found, so be sure to send over
	 * all of the necessary 404 headers and things that a browser expects
	 * to receive when that happens.
	 */
	if ( 404 === $wp->status_header ) {

		/**
		 * Here is when WordPress would normally set the 404 status of the
		 * global $wp_query variable.
		 */
		if ( ! empty( $wp->parsed ) && is_callable( array( $wp_query, 'set_404' ) ) ) {
			$wp_query->set_404();
		}

		// Send the no-cache headers so that the 404 response is not cached
		nocache_headers();
	}

	// Always send the status header with the code from above
	status_header( $wp->status_header );

/**
 * Fallback to the default WP()->handle_404() handler.
 */
} else {
	$wp->handle_404();
}

/**
 * Here is when WordPress would normally barf out all of its parsed query_vars
 * into global PHP variables.
 */
if ( ! empty( $wp->parsed ) ) {
	$wp->register_globals();
}

/**
 * Fires once the WordPress environment has been set up.
 *
 * @param WP $wp Current WordPress environment instance (passed by reference).
 */
do_action_ref_array( 'wp', array( &$wp ) );

/**
 * Output a boot.
 *
 * You can pretty much do whatever you want from here (but why would you _not_
 * want to show everyone such an incredible looking boot?!)
 */
echo "\u{1F97E}";
