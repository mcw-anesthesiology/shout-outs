export const dateTimeFormatter = new Intl.DateTimeFormat('en-US', {
	year: 'numeric',
	month: 'short',
	day: 'numeric',
	hour: 'numeric',
	minute: 'numeric',
});

export const dateFormatter = new Intl.DateTimeFormat('en-US', {
	year: 'numeric',
	month: 'short',
	day: 'numeric',
});

export const timeFormatter = new Intl.DateTimeFormat('en-US', {
	hour: 'numeric',
	minute: 'numeric',
});

export function useFormatter(formatter, x) {
	try {
		return formatter.format(x);
	} catch (e) {
		console.error(e);
		return '';
	}
}

export function formatDate(d) {
	return useFormatter(dateFormatter, d);
}

export function formatDateTime(d) {
	return useFormatter(dateTimeFormatter, d);
}

export function formatDateTimeRFC3339(d) {
	return `${d.getFullYear().toString().padStart(4, '0')}-${(d.getMonth() + 1)
		.toString()
		.padStart(2, '0')}-${d.getDate().toString().padStart(2, '0')} ${d
		.getHours()
		.toString()
		.padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')}:${d
		.getSeconds()
		.toString()
		.padStart(2, '0')}`;
}
