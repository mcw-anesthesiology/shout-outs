/** @format */

import 'whatwg-fetch';

import ShoutoutsFeed from './components/ShoutoutsFeed.svelte';
import ShoutoutForm from './components/ShoutoutForm.svelte';

const shoutoutsFeed = document.querySelector(
	'#mcw-anesth-shoutouts-feed'
);

const shoutoutsForm = document.querySelector(
	'#mcw-anesth-shoutouts-form'
);

if (shoutoutsFeed) {
	new ShoutoutsFeed({
		target: shoutoutsFeed
	});
}

if (shoutoutsForm) {
	new ShoutoutForm({
		target: shoutoutsForm
	});
}
