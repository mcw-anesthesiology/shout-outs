<div class="shoutouts-feed">
	{#each $shoutouts as shoutout (shoutout.id)}
		<Shoutout {...shoutout} />
	{/each}

	<details>
		<summary>Feed refresh options</summary>
		<form>
			<label>
				Refresh rate (seconds)
				<input type="number" bind:value={intervalSeconds} />
			</label>

			<label>
				Show most recent
				<input type="number" bind:value={limit} />
			</label>
		</form>
	</details>
</div>

<style>
	details {
		margin-top: 1em;
	}

	summary {
		cursor: pointer;
	}

	form {
		display: flex;
		justify-content: space-between;
	}

	form {
		display: flex;
		flex-wrap: wrap;
	}

	label {
		flex: 1 1;
		margin: 0.5em;
	}

	input {
		display: block;
		width: 100%;
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
