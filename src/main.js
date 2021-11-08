/** @format */

import 'whatwg-fetch';

import ShoutoutsFeed from './components/ShoutoutsFeed.svelte';
import ShoutoutsList from './components/ShoutoutsList.svelte';
import ShoutoutForm from './components/ShoutoutForm.svelte';

const shoutoutsFeed = document.querySelector('#mcw-anesth-shoutouts-feed');

const shoutoutsForm = document.querySelector('#mcw-anesth-shoutouts-form');

const shoutoutsList = document.querySelector('#mcw-anesth-shoutouts-list');

if (shoutoutsFeed) {
	new ShoutoutsFeed({
		target: shoutoutsFeed,
	});
}

if (shoutoutsForm) {
	new ShoutoutForm({
		target: shoutoutsForm,
	});
}

if (shoutoutsList) {
	new ShoutoutsList({
		target: shoutoutsList,
	});
}
