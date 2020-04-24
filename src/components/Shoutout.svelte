<div class="shoutout" class:loadingUsers>

	<table>
		<tr>
			<th>To</th>
			<td class="recipient">{recipient}</td>
		</tr>
		<tr>
			<th>For</th>
			<td class="message">{message}</td>
		</tr>
	</table>


	{#if $user && $user && ($user.admin || $user.id == created_by)}
		<div class="delete-container">

			<button type="button"
				on:click={handleDelete}
				aria-label="Delete shoutout"
				title="Delete shoutout"
				disabled={deleting}
			>
				×
			</button>

				{#if deleting}
					<span class="deleting">
						{#await deleting}
							Deleting...
						{:then}
							Successfully deleted!
						{:catch err}
							Sorry, there was an error deleting.
						{/await}
					</span>
				{/if}
		</div>
	{/if}
</div>

<style>
	.shoutout {
		max-width: 100%;
		position: relative;
		padding: 1em;
		border: 1px solid #ddd;
		border-radius: 2px;
	}

	.shoutout ~ :global(.shoutout) {
		border-top: none;
	}

	table {
		max-width: 100%;
	}

	th, td {
		padding: 0.25em;
	}

	th {
		text-align: right;
		padding-right: 2em;
		vertical-align: top;
		font-weight: normal;
		color: #666;
	}

	.message {
		word-break: break-word;
	}

	.recipient {
		font-weight: bold;
	}

	.loadingUsers .recipient {
		color: #666;
	}

	.delete-container {
		position: absolute;
		top: 10px;
		right: 10px;
	}

	.deleting {
		display: block;
		margin-top: 2em;
		text-align: right;
	}

	button {
		float: right;
		outline: none;
		background: none;

		color: rgb(199,28,25);
		border: 1px solid;
		border-color: transparent;
		border-radius: 2px;

		padding: 0.25em 0.5em;
		cursor: pointer;
	}

	button:hover {
		border-color: rgb(199,28,25);
		background-color: rgba(199,28,25,0.15);
	}
</style>

<script>
	import { user, users } from '../stores.js';
	import { BASE_URL, fetchConfig } from '../utils.js';

	export let id, recipient_id, recipient_writein, message, created_by, created_at, updated_at;

	let allUsers = [];
	let loadingUsers;
	$: loadingUsers = allUsers.length === 0;

	users.subscribe(users => {
		allUsers = users;
	});

	let deleting;

	$: recipient = recipient_id
		? loadingUsers
			? 'Loading users...'
			: (allUsers.find(u => u.id == recipient_id) || {}).name || ''
		: recipient_writein;

	function handleDelete() {
		if (id) {
			deleting = fetch(`${BASE_URL}/shoutouts?id=${encodeURIComponent(id)}`, {
				...fetchConfig,
				method: 'DELETE'
			}).then(r => {
				if (!r.ok)
					throw new Error(r.statusText);
			});
		}
	}
</script>
