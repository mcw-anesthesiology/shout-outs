/** @format */

import { readable } from 'svelte/store';
import { BASE_URL, fetchConfig } from './utils.js';

export const user = readable([], set => {
	fetch(`${BASE_URL}/user`, fetchConfig)
		.then(r => r.json())
		.then(user => {
			set(user);
		});

	return () => {};
});

export const users = readable([], set => {
	fetch(`${BASE_URL}/users`, fetchConfig)
		.then(r => r.json())
		.then(users => {
			set(users);
		});

	return () => {};
});

const SHOUTOUTS_REFRESH_INTERVAL = 5000;

export const shoutouts = watchShoutouts();

export function watchShoutouts({
	limit = null,
	offset = null,
	interval = SHOUTOUTS_REFRESH_INTERVAL
} = {}) {
	return readable([], set => {
		let url = `${BASE_URL}/shoutouts`;

		if (limit || offset) {
			const params = new URLSearchParams();
			if (limit) {
				params.set('limit', limit);
			}
			if (offset) {
				params.set('offset', offset);
			}

			url += '?' + params.toString();
		}

		const fetchShoutouts = () => {
			fetch(url, fetchConfig)
				.then(r => r.json())
				.then(shoutouts => {
					set(shoutouts);
				});
		};

		fetchShoutouts();
		const intervalId = setInterval(fetchShoutouts, interval);

		return () => {
			clearInterval(intervalId);
		};
	});
}
