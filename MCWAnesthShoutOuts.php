<?php
/*
Plugin Name: MCW Anesthesiology Shout-outs
Description: A simple WordPress plugin for giving shout-outs to others for all to see.
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
		add_shortcode('shoutouts-feed', [$this, 'shoutouts_feed_shortcode']);
		add_shortcode('shoutouts-form', [$this, 'shoutouts_form_shortcode']);
		add_shortcode('shoutouts-list', [$this, 'shoutouts_list_shortcode']);
	}

	static function extractUserData($user) {
		return [
			'id' => $user->ID,
			'name' => "{$user->first_name} {$user->last_name}"
		];
	}

	public function initRestApi() {
		remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');

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

				if (!$user || !$user->ID) {
					return null;
				}

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
				$users = array_map([$this, 'extractUserData'], array_values($users));

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

		$sql = "create table if not exists {$shoutouts} (
			id bigint(20) not null auto_increment,
			recipient_id bigint(20),
			recipient_writein varchar(255),
			message text not null,
			created_by bigint(20),
			created_by_writein varchar(255),
			anonymous boolean not null default true,
			created_at datetime not null,
			updated_at datetime not null,
			primary key  (id)
		) {$charsetCollate}";
		dbDelta($sql);
	}

	public static function getTableName($name) {
		global $wpdb;

		return $wpdb->prefix . self::DB_NAMESPACE . '_' . $name;
	}

	static function enqueueAssets() {
		wp_enqueue_script('mcw-anesth-shout-outs', plugin_dir_url(__FILE__) . 'dist/bundle.js', null, null, true);
		wp_enqueue_style('mcw-anesth-shout-outs', plugin_dir_url(__FILE__) . 'dist/bundle.css', null, null, false);
	}

	function shoutouts_feed_shortcode($atts) {
		self::enqueueAssets();

		return '<div id="mcw-anesth-shoutouts-feed"></div>';
	}

	function shoutouts_form_shortcode($atts) {
		self::enqueueAssets();

		return '<div id="mcw-anesth-shoutouts-form"></div>';
	}

	function shoutouts_list_shortcode($atts) {
		self::enqueueAssets();

		return '<div id="mcw-anesth-shoutouts-list"></div>';
	}
}

$mcwAnesthShoutOuts = new MCWAnesthShoutOuts();
