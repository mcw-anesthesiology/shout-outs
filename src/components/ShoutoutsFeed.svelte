<div class="shoutouts-feed">
	{#each $shoutouts as shoutout (shoutout.id)}
		<Shoutout {...shoutout} />
	{/each}

	<details>
		<summary>Feed refresh options</summary>
		<form>
			<p>
				Refreshing every
				<input type="number" bind:value={intervalSeconds} />
				seconds
			</p>

			<label>
				Limit
				<input type="number" bind:value={limit} />
			</label>
		</form>
	</details>
</div>

<style>
	form {
		margin-top: 1em;
		display: flex;
		justify-content: space-between;
	}

	p > input {
		width: 2em;
	}
</style>

<script>
	import Shoutout from './Shoutout.svelte';

	import { watchShoutouts } from '../stores.js';

	let limit = 10, intervalSeconds = 120;
	let interval;

	$: interval = intervalSeconds * 1000;

	$: shoutouts = watchShoutouts({ limit: Number(limit), interval });
</script>
