<div class="shoutout">
	<span class="recipient">{recipient}</span>
	<p class="message">{message}</p>
</div>

<style>
	.message {
		white-space: pre;
	}
</style>

<script>
	import { users } from '../stores.js';

	export let recipient_id, recipient_writein, message, created_at, updated_at;

	let allUsers = [];

	users.subscribe(users => {
		console.log(users);
		allUsers = users;
	});

	$: recipient = recipient_id
		? (allUsers.find(u => u.id == recipient_id) || {}).name || ''
		: recipient_writein;
</script>
