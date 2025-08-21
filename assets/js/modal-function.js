document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('mmp-ask-question-modal');
    // Button opens modal
    const openBtn = document.querySelector('.mmp-ask-question-button');
    // Button closes modal
    const closeBtn = modal.querySelector('.mmp-ask-question-close');
    // Response Div - Visual clue for telling the form successfully sent
    const responseDiv = modal.querySelector('.mmp-ask-question-response');

    // This is the element that was focused just before the modal opened, typically the button or link to open the modal
    // We keep its reference so that focus can be returned back to that element after closing the modal
    let lastActiveElement = null;

    if(!modal || !openBtn || !closeBtn) {
        return;
    }

    // This selector lists all the elements inside the modal that can receive keyboard focus
    const focusableSelector = 'a[href], button:not([disabled]), input, textarea, select, [tabindex]:not([tabindex="-1"])';

    function openModal() {
        lastActiveElement = document.activeElement;
        // Maybe form has been sent successfully and reopened
        if(responseDiv.classList.contains('mmp-error') || responseDiv.classList.contains('mmp-success')) {
            // Reset any previous state
            responseDiv.classList.remove('mmp-error', 'mmp-success');
        }
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', false);
        // Prevent page background scrolling when the modal is open
        document.body.style.overflow = 'hidden';
        // Pass the focus inside the modal and focus on the first element inside modal
        const focusables = modal.querySelectorAll(focusableSelector);
        if(focusables.length) {
            focusables[0].focus();
        }
    }

    function closeModal() {
        // The order of the code is very important.
        // Before closing the modal, we need to pass the focus last 
        if(lastActiveElement) {
            lastActiveElement.focus();
        }
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', true);
        document.body.style.overflow = '';
    }

    openBtn.addEventListener('click', (event) => {
        event.preventDefault();
        openModal();
    });

    closeBtn.addEventListener('click', (event) => {
        event.preventDefault();
        closeModal();
    })

    modal.addEventListener('click', (event) => {
        if(event.target === modal) {
            closeModal();
        }
    })

    // Keyboard Controls
    document.addEventListener('keydown', (event) => {
        if(event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }

        // Focus trap inside modal
        if(event.key === 'Tab' && modal.classList.contains('is-open')) {
            const focusables = modal.querySelectorAll(focusableSelector);
            if(!focusables.length) {
                return;
            }

            const firstModalElement = focusables[0];
            const lastModalElement = focusables[focusables.length - 1];

            if(event.shiftKey && document.activeElement === firstModalElement) {
                // If Shift + Tab is pressed and active element is the first element in then focus to last element
                event.preventDefault();
                lastModalElement.focus();
            } else if(!event.shiftKey && document.activeElement === lastModalElement) {
                // if Tab is pressed but shift is not pressed and active element is the last element in modal then focus to first element
                event.preventDefault();
                firstModalElement.focus();
            }
        }
    });
});