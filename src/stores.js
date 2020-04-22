import { readable } from 'svelte/store';
import { BASE_URL, fetchConfig } from './utils.js';

export const users = readable([], set => {
	const users = fetch(`${BASE_URL}/users`, fetchConfig).then(r => r.json()).then(users => {
		set(users);
	});

	return () => {};
});

