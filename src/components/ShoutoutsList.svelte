<div class="shoutouts-list">
	<form>
		<fieldset>
			<legend>Submitted</legend>

			<div class="dates-inputs">
				<label>
					Start
					<input type="date" bind:value={startDate} />
				</label>
				<span>–</span>
				<label>
					End
					<input type="date" bind:value={endDate} />
				</label>
			</div>
		</fieldset>
	</form>

	<table>
		<thead>
			<tr>
				<th>Submitter</th>
				<th>Recipient</th>
				<th>Message</th>
				<th>Submitted</th>
			</tr>
		</thead>
		<tbody>
			{#each shoutouts as shoutout}
				<tr>
					<th>{getSubmitter($usersMap, shoutout)}</th>
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
	form {
		display: flex;
		flex-wrap: wrap;
		justify-content: flex-end;
		margin-bottom: 1em;
	}

	label {
		display: inline-block;
	}

	label input {
		box-sizing: border-box;
		display: block;
		width: 100%;
	}

	.dates-inputs span {
		display: inline-block;
		margin: 0 0.5em;
	}

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
	import { usersMap } from '../stores.js';
	import { parseDate, BASE_URL, fetchConfig } from '../utils.js'
	import { formatDateTimeRFC3339 } from '../formatters.js';

	import RichDate from './RichDate.svelte';

	let shoutouts = [];
	let startDate, endDate;

	$: fetchShoutouts(startDate, endDate);

	function fetchShoutouts(startDate, endDate) {
		const params = new URLSearchParams();
		if (startDate) {
			params.set('start', startDate);
		}
		if (endDate) {
			params.set('end', endDate);
		}

		fetch(`${BASE_URL}/shoutouts?${params.toString()}`, fetchConfig)
			.then(r => r.json())
			.then(s => {
				shoutouts = s;
			});
	}

	function getSubmitter($usersMap, shoutout) {
		if (shoutout.anonymous) {
			return 'Anonymous';
		}

		if (shoutout.created_by_writein) {
			return shoutout.created_by_writein;
		}

		if (shoutout.created_by) {
			const submitter = $usersMap.get(shoutout.created_by);
			if (submitter) {
				return submitter.name;
			}
		}

		return 'Unknown';
	}

	function getRecipient($usersMap, shoutout) {
		if (shoutout.recipient_id) {
			const recipient = $usersMap.get(shoutout.recipient_id);
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
		'Submitter,Recipient,Message,Submitted',
		...shoutouts.map(shoutout => [
				getSubmitter($usersMap, shoutout),
				getRecipient($usersMap, shoutout),
				shoutout.message,
				formatDateTimeRFC3339(parseDate(shoutout.created_at)),
			].map(csvEscape).join(',')
		)
	].join('\n');
</script>
