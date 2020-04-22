/** @format */

import ShoutoutsFeed from './components/ShoutoutsFeed.svelte';

const shoutoutsFeed = document.querySelector(
	'#mcw-anesth-shoutouts-feed'
);

if (shoutoutsFeed) {
	createShoutoutsFeed();
}

async function createShoutoutsFeed() {
	new ShoutoutsFeed({
		target: shoutoutsFeed
	});
}
