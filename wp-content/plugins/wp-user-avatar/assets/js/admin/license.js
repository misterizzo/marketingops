(function ($) {
    /**
     * Displays Connect error message and/or reloads the page if needed.
     *
     * @param {Object} response JSON response data.
     * @param {string} response.message Error message.
     * @param {boolean} response.reload Whether to reload the page.
     */
    function onConnectError(response) {
        var feedbackEl = document.getElementById(
            'ppress-connect-license-feedback'
        );

        var submitButtonEl = document.getElementById(
            'ppress-connect-license-submit'
        );

        // Toggle feedback.
        if (response.message) {
            feedbackEl.innerText = response.message;
            feedbackEl.classList.remove('ppress-license-message--valid');
            feedbackEl.classList.add('ppress-license-message--invalid');
            feedbackEl.style.display = 'block';
        } else {
            feedbackEl.style.display = 'none';
        }

        // Enable submit button again if the page is not reloading.
        if (!response.reload) {
            submitButtonEl.disabled = false;
            submitButtonEl.innerText = submitButtonEl.dataset.connect;
        } else {
            setTimeout(function () {
                window.location.reload();
            }, 2000);
        }
    }

    /**
     * Displays Connect error message and/or reloads or redirects the page if needed.
     *
     * @param {Object} response JSON response data.
     * @param {string} response.message Error message.
     * @param {boolean} response.reload Whether to reload the page.
     * @param {string} response.url URL to redirect to.
     */
    function onConnectSuccess(response) {
        var feedbackEl = document.getElementById(
            'ppress-connect-license-feedback'
        );

        // Toggle feedback.
        if (response.message) {
            feedbackEl.innerText = response.message;
            feedbackEl.classList.remove('ppress-license-message--invalid');
            feedbackEl.classList.add('ppress-license-message--valid');
            feedbackEl.style.display = 'block';
        } else {
            feedbackEl.style.display = 'none';
        }

        // Redirect if the current page is not being reloaded.
        if (!response.reload) {
            window.location = response.url;
        } else {
            setTimeout(function () {
                window.location.reload();
            }, 2000);
        }
    }

    /**
     * Submits the Lite Connect data.
     */
    function onConnect() {
        var licenseKeyEl = document.getElementById(
            'ppress-connect-license-key'
        );

        var nonceEl = document.getElementById('ppress-connect-license-nonce');

        var submitButtonEl = document.getElementById(
            'ppress-connect-license-submit'
        );

        // Disable submit.
        submitButtonEl.disabled = true;
        submitButtonEl.innerText = submitButtonEl.dataset.connecting;

        // Get the URL.
        wp.ajax.send('ppress_connect_url', {
            data: {
                nonce: nonceEl.value,
                key: licenseKeyEl.value,
            },
            // Handle success (redirect).
            success: onConnectSuccess,

            // Handle error (show error).
            error: onConnectError,
        });
    }

    /**
     * Binds the Lite Connect form events.
     */
    $(function () {

        var licenseKeyEl = document.getElementById(
            'ppress-connect-license-key'
        );

        if (!licenseKeyEl) return;

        var submitButtonEl = document.getElementById(
            'ppress-connect-license-submit'
        );

        // Start the process if the "Enter" key is pressed while the license input is focused.
        licenseKeyEl.addEventListener('keypress', function (e) {
            if (e.key !== 'Enter') return;

            e.preventDefault();
            onConnect();
        });

        // Start the process if the submit button is clicked.
        submitButtonEl.addEventListener('click', function (e) {
            e.preventDefault();
            submitButtonEl.disabled = true;
            onConnect();
        });
    });

})(jQuery);