<div class="shoutouts-list">
	<table>
		<thead>
			<tr>
				<th>Recipient</th>
				<th>Message</th>
				<th>Created</th>
			</tr>
		</thead>
		<tbody>
			{#each $shoutouts as shoutout}
				<tr>
					<th>{getRecipient($usersMap, shoutout)}</th>
					<td>{shoutout.message}</td>
					<td><RichDate date={shoutout.created_at} showTime /></td>
				</tr>
			{/each}
		</tbody>
	</table>

	<div class="download-container">
		<a href="data:text/csv,{encodeURIComponent(csv)}" download>
			Download as CSV
		</a>
	</div>
</div>

<style>
	table {
		border-collapse: collapse;
		width: 100%;
	}

	th, td {
		padding: 0.5em 1em;
		border: 1px solid #ccc;
	}

	th {
		text-align: left;
	}

	.download-container {
		margin: 1em;
		text-align: center;
	}
</style>

<script>
	import { shoutouts, usersMap } from '../stores.js';
	import { parseDate } from '../utils.js'
	import { formatDateTimeRFC3339 } from '../formatters.js';

	import RichDate from './RichDate.svelte';

	function getRecipient($usersMap, shoutout) {
		if (shoutout.recipient_id) {
			const recipient = $usersMap.get(Number(shoutout.recipient_id));
			if (recipient) {
				return recipient.name;
			}
		}

		if (shoutout.recipient_writein) {
			return shoutout.recipient_writein;
		}

		return 'Unknown';
	}

	function csvEscape(value) {
		return `"${value.replace('"', '""')}"`;
	}

	let csv;
	$: csv = [
		'Recipient,Message,Created',
		...$shoutouts.map(shoutout => [
				getRecipient($usersMap, shoutout),
				shoutout.message,
				formatDateTimeRFC3339(parseDate(shoutout.created_at)),
			].map(csvEscape).join(',')
		)
	].join('\n');
</script>
