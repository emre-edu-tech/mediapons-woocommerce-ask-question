document.addEventListener('DOMContentLoaded', () => {
    const modalButton = document.querySelector('.mmp-ask-question-button');
    const modal = document.getElementById('mmp-ask-question-modal');
    const questionForm = document.getElementById('mmp-ask-question-form');
    
    if (!modalButton || !modal || !questionForm) {
        return;
    }

    questionForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const name = questionForm.querySelector('#mmp-name');
        const email = questionForm.querySelector('#mmp-email');
        const message = questionForm.querySelector('#mmp-message');
        const product_id = questionForm.querySelector('input[name="mp_wc_ask_question_product_id"]');
        const submitBtn = questionForm.querySelector('.mmp-ask-question-submit');
        const submitBtnText = submitBtn.querySelector('.mmp-btn-text');
        const responseDiv = questionForm.querySelector('.mmp-ask-question-response');
        const turnstileToken = window.turnstile.getResponse();
        const turnstileWidgetId = questionForm.getAttribute('data-cf-turnstile');

        // Reset any previous state
        responseDiv.classList.remove('mmp-error', 'mmp-success');

        // Show Spinner
        submitBtn.classList.add('loading');
        submitBtnText.textContent = mmpData.sending;    // comes from wp_localize_script

        // Clear Reponse Div
        responseDiv.textContent = ''
        // ajax_url comes from wp_localize_script
        fetch(mmpData.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'mmp_submit_ask_question',
                name: name.value,
                email: email.value,
                message: message.value,
                product_id: product_id.value,
                turnstile_response: turnstileToken,
            })
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.classList.remove('loading');
            submitBtnText.textContent = mmpData.send_question;
            if(data.success) {
                responseDiv.classList.add('mmp-success');
                responseDiv.textContent = data.message || mmpData.question_sent;
                // Clean all the form inputs
                questionForm.reset();
            } else {
                responseDiv.classList.add('mmp-error');
                responseDiv.textContent = data.message || mmpData.fill_fields;
            }
        })
        .catch(() => {
            submitBtnText.textContent = mmpData.send_question;
            responseDiv.classList.add('mmp-error');
            responseDiv.textContent = data.message || mmpData.error_occured;
        })
        .finally(() => {
            submitBtn.classList.remove('loading');
            // Important to reset the reCAPTCHA since the user can send the form multiple times
            if(turnstileWidgetId) {
                window.turnstile.reset(turnstileWidgetId);
            } else {
                window.turnstile.reset();
            }
        });
    })
});