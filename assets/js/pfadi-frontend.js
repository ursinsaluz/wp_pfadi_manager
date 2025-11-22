document.addEventListener('DOMContentLoaded', function () {
    const subscribeForm = document.getElementById('pfadi-subscribe-form');
    const messageContainer = document.getElementById('pfadi-subscribe-message');

    if (subscribeForm) {
        subscribeForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(subscribeForm);
            formData.append('action', 'pfadi_subscribe');
            formData.append('nonce', pfadi_ajax.nonce);

            // Disable button
            const submitBtn = subscribeForm.querySelector('input[type="submit"]');
            const originalBtnText = submitBtn.value;
            submitBtn.disabled = true;
            submitBtn.value = 'Bitte warten...';
            messageContainer.innerHTML = '';
            messageContainer.className = '';

            fetch(pfadi_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageContainer.innerHTML = data.data.message;
                        messageContainer.className = 'pfadi-message success';
                        subscribeForm.reset();
                    } else {
                        messageContainer.innerHTML = data.data.message;
                        messageContainer.className = 'pfadi-message error';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    messageContainer.innerHTML = 'Das hat leider nicht geklappt. Bitte versuche es später noch einmal oder wende dich an admin@alvier.ch.';
                    messageContainer.className = 'pfadi-message error';
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.value = originalBtnText;
                });
        });
    }

    // Tabs handling
    const tabs = document.querySelectorAll('.pfadi-tabs a');
    const contentContainer = document.getElementById('pfadi-activities-content');

    if (tabs.length > 0 && contentContainer) {
        tabs.forEach(tab => {
            tab.addEventListener('click', function (e) {
                e.preventDefault();

                // Update active state
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const unit = this.getAttribute('data-unit');
                const view = contentContainer.getAttribute('data-view');

                // Loading state
                contentContainer.classList.add('loading');

                // Update URL
                const url = new URL(window.location);
                if (unit) {
                    url.searchParams.set('pfadi_unit', unit);
                } else {
                    url.searchParams.delete('pfadi_unit');
                }
                window.history.pushState({}, '', url);

                // Fetch activities
                const formData = new FormData();
                formData.append('action', 'pfadi_load_activities');
                formData.append('unit', unit);
                formData.append('view', view);
                formData.append('nonce', pfadi_ajax.nonce);

                fetch(pfadi_ajax.ajax_url, {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            contentContainer.innerHTML = data.data.content;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    })
                    .finally(() => {
                        contentContainer.classList.remove('loading');
                    });
            });
        });

        // Handle back/forward browser buttons
        window.addEventListener('popstate', function () {
            const urlParams = new URLSearchParams(window.location.search);
            const unit = urlParams.get('pfadi_unit') || '';

            const tabToActivate = document.querySelector(`.pfadi-tabs a[data-unit="${unit}"]`);
            if (tabToActivate) {
                tabToActivate.click();
            }
        });
    }
});
