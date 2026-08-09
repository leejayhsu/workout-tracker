const browserTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
const currentTimezone = document.body.dataset.userTimezone;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

if (browserTimezone && currentTimezone === 'UTC' && csrfToken) {
    fetch('/timezone', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json',
        },
        body: JSON.stringify({ timezone: browserTimezone }),
    }).then((response) => {
        if (response.ok) {
            window.location.reload();
        }
    });
}
