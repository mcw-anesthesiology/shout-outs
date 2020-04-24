<form class="shoutouts-form" bind:this={form} on:submit={handleSubmit}>
	<fieldset>
		<legend>Recipient</legend>

		<div>
			<label>
				Select name
				{#if supportsCssVars}
					<Select {items} bind:selectedValue={selectedRecipient}
						isDisabled={submitting}
						noOptionsMessage="Loading recipient list..."
					/>
				{:else}
					<select name="recipient_id" bind:value={recipient_id}
						disabled={submitting}
					>
						<option value=""></option>
						{#each items as item}
							<option value={item.value}>{item.label}</option>
						{/each}
					</select>
				{/if}
			</label>

			<span>
				or
			</span>

			<label>
				Write-in
				<input type="text" name="recipient_writein" bind:value={recipient_writein} disabled={submitting} />
			</label>
		</div>

		<aside>
			{#if hasTooManyRecipients}
				<span class="alert">
					Please select or write in a recipient, not both
				</span>
			{:else if message && !hasRecipient}
				<span class="alert">
					Please select or write in a recipient
				</span>
			{/if}
		</aside>
	</fieldset>

	<label>
		Message
		<textarea name="message" bind:value={message} disabled={submitting}></textarea>
	</label>

	{#if submitting}
		{#await submission}
			<span>Submitting</span>
		{:then}
			<span>Successfully submitted!</span>

			<button type="button" on:click={handleReset}>
				Submit another
			</button>
		{:catch err}
			<span class="alert">
				Sorry, there was a problem submitting your shout-out.
			</span>

			<button type="submit" disabled={!isComplete || submitting}>
				Try again
			</button>
		{/await}
	{:else}
		<button type="submit" disabled={!isComplete || submitting}>
			Shout out!
		</button>
	{/if}
</form>

<style>
	form {
		padding: 0.5em;
	}

	fieldset {
		margin-bottom: 1em;
	}

	form > button {
		display: block;
		margin: 1em auto;
	}

	aside {
		padding: 0.5em;
		height: 30px;
	}

	.alert {
		display: block;
		padding: 0.5em;
		text-align: center;
		background-color: rgba(215,215,0,0.5);
	}

	fieldset > div {
		display: flex;
		flex-direction: row;
		flex-wrap: wrap;
		align-items: center;
		justify-content: center;
	}

	fieldset > div > span {
		display: block;
		margin: 1em;
		width: 200px;
		flex-shrink: 2;
		text-align: center;
	}

	fieldset > div > label {
		width: 200px;
		max-width: 100%;
		flex-grow: 1;
	}

	fieldset > div > label > input,
	fieldset > div > label > select {
		height: 42px;
		height: var(--height, 42px);
	}

	label > input,
	label > textarea,
	label > select {
		box-sizing: border-box;
		display: block;
		width: 100%;
	}
</style>

<script>
	import Select from 'svelte-select';

	import { users } from '../stores.js';
	import { BASE_URL, fetchConfig } from '../utils.js';

	let supportsCssVars = window.CSS && window.CSS.supports('color', 'var(--test)');

	let items = [];
	users.subscribe(users => {
		items = users.map(user => ({
			value: user.id,
			label: user.name
		}));
	});

	let form;
	let recipient_id, recipient_writein, message = '';
	let selectedRecipient;

	$: if (supportsCssVars && selectedRecipient) {
		recipient_id = selectedRecipient.value;
	} else {
		recipient_id = undefined;
	}

	let isComplete, hasRecipient;
	$: hasRecipient = recipient_id || recipient_writein;
	$: hasTooManyRecipients = recipient_id && recipient_writein;
	$: isComplete = hasRecipient && !hasTooManyRecipients && message;

	let submitting = false, submission;

	function handleReset() {
		selectedRecipient = null;
		recipient_id = undefined;
		recipient_writein = undefined;
		message = '';
		submitting = false;
		submission = null;

		form.reset();
	}

	function handleSubmit(event) {
		event.preventDefault();
		submitting = true;

		submission = fetch(`${BASE_URL}/shoutouts`, {
			...fetchConfig,
			method: 'POST',
			body: JSON.stringify({
				recipient_id,
				recipient_writein,
				message
			})
		}).then(r => {
			if (!r.ok) {
				throw new Error(r.statusText);
			}
		});
	}
</script>
