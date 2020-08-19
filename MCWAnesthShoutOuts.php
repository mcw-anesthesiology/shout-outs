<?php
/*
Plugin Name: MCW Anesthesiology Shout-outs
Author: Jacob Mischka
Version: 0.1.0
License: GPL-3.0
*/

namespace MCWAnesthShoutOuts;

defined ('ABSPATH') or die('No');

require_once('ShoutOutController.php');

use WP_Error;

class MCWAnesthShoutOuts {
	const API_NAMESPACE = 'mcw-anesth-shout-outs/v1';
	const DB_NAMESPACE = 'mcw_anesth_shout_outs';

	public function __construct() {
		register_activation_hook(__FILE__, [$this, 'createTables']);

		add_action('init', [$this, 'initPlugin']);
		add_action('rest_api_init', [$this, 'initRestApi']);
	}

	const ALLOWED_ORIGINS = [];

	static function matchesNamespace($route) {
		return strpos($route, self::API_NAMESPACE) === 1;
	}

	public function initPlugin() {
		wp_enqueue_script('mcw-anesth-shout-outs', plugin_dir_url(__FILE__) . 'dist/bundle.js', null, null, true);
		wp_enqueue_style('mcw-anesth-shout-outs', plugin_dir_url(__FILE__) . 'dist/bundle.css', null, null, false);


		add_shortcode('shoutouts-feed', [$this, 'shoutouts_feed_shortcode']);
		add_shortcode('shoutouts-form', [$this, 'shoutouts_form_shortcode']);
	}

	static function extractUserData($user) {
		return [
			'id' => $user->ID,
			'name' => "{$user->first_name} {$user->last_name}"
		];
	}

	public function initRestApi() {
		remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
		add_filter('rest_pre_dispatch', function($result, $server, $request) {
			if (self::matchesNamespace($request->get_route())) {
				$user = wp_get_current_user();
				if (!$user || !$user->ID)
					return new WP_Error('unauthorized', 'Unauthorized', ['status' => 401]);
			}

			return $result;
		}, 10, 4);

		add_filter('rest_pre_serve_request', function($served, $response, $request, $server) {
			if (self::matchesNamespace($request->get_route())) {
				header('Access-Control-Allow-Credentials: true');
				header('Access-Control-Allow-Methods: POST, GET, PATCH, DELETE');
				header('Access-Control-Allow-Headers: Authorization, Content-Type, X-WP-Nonce');

				$origin = get_http_origin();
				$allowOrigin = esc_url_raw(site_url());

				if ($origin && in_array($origin, self::ALLOWED_ORIGINS)) {
					$allowOrigin = $origin;
				}
				header('Access-Control-Allow-Origin: ' . $allowOrigin);
			}

		}, 10, 4);

		register_rest_route(self::API_NAMESPACE, '/user', [
			'methods' => ['GET'],
			'callback' => function($request) {
				$user = wp_get_current_user();

				$return = self::extractUserData($user);

				if (in_array('administrator', $user->roles)) {
					$return['admin'] = true;
				}

				return $return;
			}
		]);
		register_rest_route(self::API_NAMESPACE, '/users', [
			'methods' => ['GET'],
			'callback' => function($request) {
				$users = get_users();
				$users = array_map([self, 'extractUserData'], array_values($users));

				usort($users, function ($a, $b) {
					if ($a['name'] == $b['name']) {
						return 0;
					}

					return $a['name'] < $b['name'] ? -1 : 1;
				});

				return $users;
			}
		]);

		register_rest_route(self::API_NAMESPACE, '/shoutouts', [
			'methods' => ['GET', 'POST', 'PATCH', 'DELETE'],
			'callback' => [ShoutOutController::class, 'handleRequest']
		]);
	}

	public function createTables() {
		global $wpdb;

		$shoutouts = self::getTableName('shoutouts');

		$charsetCollate = $wpdb->get_charset_collate();
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		$sql = "CREATE TABLE IF NOT EXISTS {$shoutouts} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			recipient_id bigint(20),
			recipient_writein varchar(255),
			message text NOT NULL,
			created_by bigint(20) NOT NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY  (id)
		) {$charsetCollate}";
		dbDelta($sql);
	}

	public static function getTableName($name) {
		global $wpdb;

		return $wpdb->prefix . self::DB_NAMESPACE . '_' . $name;
	}

	function shoutouts_feed_shortcode($atts) {
		return '<div id="mcw-anesth-shoutouts-feed"></div>';
	}

	function shoutouts_form_shortcode($atts) {
		return '<div id="mcw-anesth-shoutouts-form"></div>';
	}
}

$mcwAnesthShoutOuts = new MCWAnesthShoutOuts();
