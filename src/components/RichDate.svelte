<time datetime={dateObj?.toISOString()}>
	{useFormatter(formatter, dateObj)}
</time>

<script>
	import { parseDate } from '../utils.js';
	import { useFormatter, dateFormatter, dateTimeFormatter, timeFormatter } from '../formatters.js';

	export let date;
	export let showTime = false;
	export let timeOnly = false;
	let formatter;

	let dateObj;
	$: dateObj = date instanceof Date ? date : parseDate(date);

	$: formatter = timeOnly ? timeFormatter : showTime ? dateTimeFormatter : dateFormatter;
</script>
