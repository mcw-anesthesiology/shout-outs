<?php

namespace MCWAnesthShoutOuts;

require_once('MCWAnesthShoutOuts.php');

use WP_Error;

class ShoutOutController {
	const TABLE = 'shoutouts';
	const COLUMNS = [
		'id',
		'recipient_id',
		'recipient_writein',
		'message',
		'created_by',
		'created_by_writein',
		'anonymous',
		'created_at',
		'updated_at'
	];
	const REQUIRED = [
		'message'
	];
	const JSON_COLUMNS = [];
	const BOOLEAN_COLUMNS = ['anonymous'];

	public static function handleRequest($request) {
		switch ($request->get_method()) {
		case 'GET':
			return static::get($request);
		case 'POST':
			return static::post($request);
		case 'PATCH':
			return static::patch($request);
		case 'DELETE':
			return static::delete($request);
		}
	}

	static function getParams($params) {
		return
			array_map(
				function ($val) {
					if (is_array($val)) {
						return json_encode($val);
					}

					if (is_bool($val)) {
						return $val ? 1 : 0;
					}

					return $val;
				},
				array_filter(
					$params,
					function ($key) {
						return in_array($key, static::COLUMNS);
					},
					ARRAY_FILTER_USE_KEY
				)
			);
	}

	static function transformShoutout($shoutout) {
		$shoutout->anonymous = boolval($shoutout->anonymous);

		if (!empty($shoutout->recipient_id)) {
			$shoutout->recipient_id = intval($shoutout->recipient_id);
		}

		if ($shoutout->anonymous) {
			$shoutout->created_by = null;
		} else if (!empty($shoutout->created_by)) {
			$shoutout->created_by = intval($shoutout->created_by);
		}

		return $shoutout;
	}

	public static function get($request) {
		global $wpdb;

		$table = MCWAnesthShoutOuts::getTableName(static::TABLE);

		$id = $request->get_param('id');
		if (!empty($id)) {
			$query = "SELECT * FROM {$table} WHERE id = %d";
			$shoutout = $wpdb->get_row($wpdb->prepare($query, $id));

			if (!empty($shoutout))
				return self::transformShoutout($shoutout);

			return new WP_Error('not-found', 'Not found', ['status' => 404]);
		}

		$query = "SELECT * FROM {$table}";
		$vals = [];

		$after = $request->get_param('start') ?? $request->get_param('after');
		if (!empty($after)) {
			$whereClauses[] = 'date(created_at) >= %s';
			$vals[] = $after;
		}

		$before = $request->get_param('end') ?? $request->get_param('before');
		if (!empty($before)) {
			$whereClauses[] = 'date(created_at) <= %s';
			$vals[] = $before;
		}

		if (!empty($whereClauses)) {
			$query .= ' WHERE ' . implode(' and ', $whereClauses);
		}

		$query .= ' ORDER BY id DESC';

		if (!empty($request->get_param('limit'))) {
			$query .= ' limit %d';
			$vals[] = $request->get_param('limit');

			if (!empty($request->get_param('offset'))) {
				$query .= ' OFFSET %d';
				$vals[] = $request->get_param('offset');
			}

		}

		if (!empty($vals)) {
			$query = $wpdb->prepare($query, $vals);
		}

		return array_map('MCWAnesthShoutOuts\ShoutOutController::transformShoutout', $wpdb->get_results($query));
	}

	public static function post($request) {
		global $wpdb;

		$params = $request->get_params();
		foreach (static::REQUIRED as $param) {
			if (empty($params[$param])) {
				return new WP_Error('missing_params', 'Missing required parameters', ['status' => 400]);
			}
		}
		if (empty($params['recipient_id']) && empty($params['recipient_writein'])) {
			return new WP_Error('missing_params', 'Missing required parameters', ['status' => 400]);
		}

		if (!empty($params['recipient_id'])) {
			$recipient = get_user_by('ID', $params['recipient_id']);

			if (empty($recipient)) {
				return new WP_Error('invalid-recipient', 'Recipient does not exist', ['status' => 404]);
			}
		}

		$params = static::getParams($request->get_params());

		$user = wp_get_current_user();
		if ($user && $user->ID) {
			$params['created_by'] = $user->ID;
		}

		$table = MCWAnesthShoutOuts::getTableName(static::TABLE);
		$wpdb->insert($table,
			array_merge(
				$params,
				[
					'created_at' => date('c'),
					'updated_at' => date('c')
				]
			)
		);

		$query = "SELECT * FROM {$table} WHERE id = %d";
		return self::transformShoutout($wpdb->get_row($wpdb->prepare($query, [$wpdb->insert_id])));
	}

	public static function patch($request) {
		global $wpdb;

		$id = $request->get_param('id');

		if (empty($id)) {
			return new WP_Error('missing_params', 'Missing required parameters', ['status' => 400]);
		}

		$table = MCWAnesthShoutOuts::getTableName(static::TABLE);
		$user = wp_get_current_user();

		if (in_array('administrator', $user->roles)) {

			$wpdb->update(
				$table,
				array_merge(
					static::getParams($request->get_params()),
					[
						'updated_at' => date('c')
					]
				),
				['id' => $id]
			);
		} else {
			$wpdb->update(
				$table,
				array_merge(
					static::getParams($request->get_params()),
					[
						'updated_at' => date('c')
					]
				),
				['id' => $id, 'created_by' => $user->ID]
			);

		}

		$query = "SELECT * FROM {$table} WHERE id = %d";
		return self::transformShoutout($wpdb->get_row($wpdb->prepare($query, [$id])));
	}

	public static function delete($request) {
		global $wpdb;

		$id = $request->get_param('id');
		if (empty($id))
			return new WP_Error('missing_params', 'Missing required parameters', ['status' => 400]);

		$table = MCWAnesthShoutOuts::getTableName(static::TABLE);
		$user = wp_get_current_user();

		if (in_array('administrator', $user->roles)) {
			$wpdb->delete($table, ['id' => $id]);
		} else {
			$wpdb->delete($table, ['id' => $id, 'created_by' => $user->ID]);
		}
	}
}
