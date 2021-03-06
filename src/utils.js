/** @format */

export const BASE_URL = '/wp-json/mcw-anesth-shout-outs/v1';

export function getNonce() {
	try {
		return document.querySelector('meta[name="wp_rest"]').content;
	} catch (err) {
		console.error('Error getting nonce', err);
	}
}

const headers = {
	'Content-Type': 'application/json',
};

const nonce = getNonce();
if (nonce) {
	headers['X-WP-NONCE'] = nonce;
}

export const fetchConfig = {
	headers,
	credentials: 'same-origin',
};

export function parseBoolString(s) {
	if (s === '0' || s === 'false') return false;

	return Boolean(s);
}

export function parseDate(date) {
	const d = new Date(date);
	d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
	return d;
}

export function toISODate(date) {
	const s = date.toISOString();
	return s.substring(0, s.indexOf('T'));
}
