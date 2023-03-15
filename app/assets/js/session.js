'use strict'

/**
 * --------------------------------------------------------------------------
 * Popover.
 * --------------------------------------------------------------------------
 */
import("./helpers.min.js").then(module => module.buildPopover(
    'popover--sign-in-info',
    'Las credenciales de acceso son las registradas en el Active Directory.',
    'info'
))

/**
 * --------------------------------------------------------------------------
 * Toggle show - hide password.
 * --------------------------------------------------------------------------
 */
document.querySelector('input[type="password"]+button').addEventListener('click', function () {
    let input = this.previousElementSibling,
        icon = this.querySelector('svg'),
        iconData = icon.getAttribute('data-icon')

    input.type = input.type === 'password' ? 'text' : 'password';
    iconData === 'lock' ?
        icon.setAttribute('data-icon', 'unlock')
        : icon.setAttribute('data-icon', 'lock')
})

/**
 * --------------------------------------------------------------------------
 * Client side validation.
 * --------------------------------------------------------------------------
 */
import("./helpers.min.js").then(module => module.validateForm(1))

/**
 * --------------------------------------------------------------------------
 * Submit form.
 * --------------------------------------------------------------------------
 */
const form = $('#form--sign-in'),
      buttonSubmit = $('#button__submit-form--sign-in')

const buttonStates = {
    'start': 'Iniciar Sesión',
    'processing': '<i class="me-2 fa-solid fa-spinner fa-spin-pulse"></i>Procesando'
}

buttonSubmit.on('click', signIn)

function signIn() {
    let request =$.ajax({
        method: 'POST',
        url: 'Session/signIn',
        data: form.serializeArray(),
        dataType: 'JSON',
        beforeSend: function() {
            buttonSubmit.html(buttonStates.processing).prop('disabled', true)
        }
    })

    let title,
        description,
        type

    request.done(function() {
        window.location.reload()
    })

    request.fail(function(request) {
        if (!request.hasOwnProperty('responseJSON')) {
            title = 'Request Execution'
            description = 'An error occurred while executing the request'
            type = 'danger'

            import('./helpers.min.js').then(module => module.buildToast(title, description, type))

            return
        }

        if (!request.responseJSON.data) {
            title = request.responseJSON.title
            description = request.responseJSON.description
            type = request.responseJSON.type

            import('./helpers.min.js').then(module => module.buildToast(title, description, type))

            return
        }

        $.each(request.responseJSON.data.validation, function (name, state) {
            let input = $(`input[name='${name}']`),
                siblingContent = `<small class="invalid-feedback fw-bold">${state.message}</small>`

            input.parent().find('small').remove()
            input.removeClass('is-invalid is-valid').addClass(state.result)

            if (state.result === 'is-invalid') {
                input.parent().append(siblingContent)
            }
        })
    })

    request.always(function(response) {
        buttonSubmit.html(buttonStates.start).prop('disabled', false)
    })
}
