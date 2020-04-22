<div class="shoutouts-feed">
	{#await shoutoutsPromise}
		<p>Loading...</p>
	{:then shoutouts}
		{#each shoutouts as shoutout}
			<Shoutout {...shoutout} />
		{/each}
	{:catch err}
		<p>Error!</p>
	{/await}
</div>

<script>
	import Shoutout from './Shoutout.svelte';

	import { BASE_URL, fetchConfig } from '../utils.js';

	let limit = 10;
	let offset = 0;

	let params = '';
	$: {
		const newParams = new URLSearchParams();
		if (limit) {
			newParams.set('limit', limit);
		}

		if (offset) {
			newParams.set('offset', offset);
		}

		params = newParams;
	}

	let shoutoutsPromise = fetch(`${BASE_URL}/shoutouts?${params.toString()}`, fetchConfig).then(r => r.json());
</script>
