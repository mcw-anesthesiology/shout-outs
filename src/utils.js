export const BASE_URL = '/wp-json/mcw-anesth-shout-outs/v1';

export function getNonce() {
	try {
		return document.querySelector('meta[name="wp_rest"]').content;
	} catch (err) {
		console.error('Error getting nonce', err);
	}
}

const headers = {
	'Content-Type': 'application/json'
};

const nonce = getNonce();
if (nonce) {
	headers['X-WP-NONCE'] = nonce;
}

export const fetchConfig = {
	headers,
	credentials: 'same-origin'
};

