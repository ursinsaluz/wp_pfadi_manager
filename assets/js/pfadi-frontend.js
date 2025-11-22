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

    if (tabs.length > 0) {
        tabs.forEach(tab => {
            tab.addEventListener('click', function (e) {
                e.preventDefault();

                const unit = this.getAttribute('data-unit');

                // 1. Update active state for ALL tabs on the page matching this unit
                // First remove active from all
                document.querySelectorAll('.pfadi-tabs a').forEach(t => t.classList.remove('active'));
                // Then add to all matching unit
                document.querySelectorAll(`.pfadi-tabs a[data-unit="${unit}"]`).forEach(t => t.classList.add('active'));

                // 2. Update ALL content containers
                const containers = document.querySelectorAll('.pfadi-activities-content');

                containers.forEach(contentContainer => {
                    const view = contentContainer.getAttribute('data-view');

                    // Loading state
                    contentContainer.classList.add('loading');

                    // Fetch activities for this specific container (view might differ)
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

                // Update URL (just once)
                const url = new URL(window.location);
                if (unit) {
                    url.searchParams.set('pfadi_unit', unit);
                } else {
                    url.searchParams.delete('pfadi_unit');
                }
                window.history.pushState({}, '', url);
            });
        });
    }

    // Handle back/forward browser buttons
    window.addEventListener('popstate', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const unit = urlParams.get('pfadi_unit') || '';

        const tabToActivate = document.querySelector(`.pfadi-tabs a[data-unit="${unit}"]`);
        if (tabToActivate) {
            tabToActivate.click();
        }
    });

    // Side Tabs View Handling
    document.addEventListener('click', function (e) {
        if (e.target.closest('.pfadi-list-item')) {
            const item = e.target.closest('.pfadi-list-item');
            const container = item.closest('.pfadi-list-view');
            const id = item.getAttribute('data-id');

            // Remove active from all items in this container
            container.querySelectorAll('.pfadi-list-item').forEach(i => i.classList.remove('active'));
            container.querySelectorAll('.pfadi-list-content').forEach(c => c.classList.remove('active'));

            // Add active to clicked item and corresponding content
            item.classList.add('active');
            container.querySelector(`.pfadi-list-content[data-id="${id}"]`).classList.add('active');
        }
    });
});
