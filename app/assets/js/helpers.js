'use strict'

/**
 * --------------------------------------------------------------------------
 * Popover.
 * --------------------------------------------------------------------------
 */

/**
 * Initializes the popover component.
 *
 * @param {string}  id       Popover container identifier.
 * @param {string}  content  Popover content.
 * @param {string}  theme    Appearance of the popover based on the bootstrap theme.
 *
 * @return void
 */
export const buildPopover = (id, content, theme) => {
    new bootstrap.Popover(
        document.getElementById(id),
        {
            content: content,
            template: `
                <div class="popover border-0 rounded" role="tooltip">
                    <div class="mb-0 popover-body alert alert-${theme} rounded text-center"></div>
                </div>
            `
        }
    )
}

/**
 * --------------------------------------------------------------------------
 * Toast notification.
 * --------------------------------------------------------------------------
 */

/**
 * Initializes a push notification.
 *
 * @param {string} title   Title of the notification.
 * @param {string} message Notification message.
 * @param {string} theme   Appearance of the toast based on the bootstrap theme.
 * @param {int}    delay   Time in ms in which the notification will be closed.
 *
 * @return void
 */
export const buildToast = (title, message, theme, delay = 3000) => {
    let currentTime = new Date(),
        hour = ('0' + currentTime.getHours()).slice(-2),
        minutes = ('0' + currentTime.getMinutes()).slice(-2),
        seconds = ('0' + currentTime.getSeconds()).slice(-2),
        fullTime = `${hour}:${minutes}:${seconds}`

    let colorBorder = `border-${theme}`,
        colorText = `text-${theme}`

    let toastTemplate = `
        <div id="toast--notification" class="mb-2 border ${colorBorder} toast" role="alert"
             aria-live="assertive" aria-atomic="true">
            <div class="toast-header ${colorBorder} ${colorText}">
                <i class="me-2 fa-regular fa-bell"></i>
                <strong class="me-auto">${title}</strong>
                <small class="${colorText}">${fullTime}</small>
                <button type="button" class="btn btn-sm btn-link ${colorText}" data-bs-dismiss="toast">
                    <i class="fa-regular fa-circle-xmark fa-lg"></i>
                </button>
            </div>
            <div class="toast-body bg-white ${colorText}">${message}</div>
        </div>
    `;

    const toastWrapper = document.createElement('template');
    toastWrapper.innerHTML = toastTemplate.trim();

    const toast = toastWrapper.content.firstChild;
    document.querySelector('.div__toast--custom-notification').appendChild(toast);

    new bootstrap.Toast(toast, {autohide: true, delay: delay}).show();

    toast.addEventListener('hidden.bs.toast', () => {
        document.querySelector('.div__toast--custom-notification').removeChild(toast)
    })
}

/**
 * --------------------------------------------------------------------------
 * Client side validation.
 * --------------------------------------------------------------------------
 */
const formElements = document.querySelectorAll('form input, form select')

/**
 * Validates elements on a form.
 *
 * @param {int} isInGroup Flag indicating whether the element is encapsulated in a group.
 *
 * @return void
 */
export const validateForm = (isInGroup = 0) => {
    formElements.forEach(element => {
        element.addEventListener('blur', (event) => {
            event.target.classList.remove('is-valid', 'is-invalid')

            let parent,
                sibling

            if (isInGroup) {
                parent = element.closest('.input-group')

                if (parent.querySelectorAll('small').length !== 0) {
                    parent.lastElementChild.remove()
                }
            } else {
                sibling = element.nextElementSibling;

                if (sibling !== null) {
                    sibling.remove()
                }
            }

            if (!event.target.checkValidity()) {
                event.target.classList.add('is-invalid')

                let feedbackElement = document.createElement('small')
                feedbackElement.classList.add('invalid-feedback')
                feedbackElement.classList.add('fw-bold')

                if (isInGroup) {
                    parent.appendChild(feedbackElement)
                } else {
                    element.after(feedbackElement)
                }

                feedbackElement.innerHTML = event.target.validationMessage
            }
        })
    })
}
