'use strict';

const modal = new bootstrap.Modal(document.getElementById("modal__update--user-info"), {});

/**
 * --------------------------------------------------------------------------
 * Dashboard construction.
 * --------------------------------------------------------------------------
 */

/**
 * Fetches the information of the user with active session.
 *
 * @return void
 */
function getUserInformation() {
    let request = $.ajax({
        url: 'Dashboard/getUserInformation',
        method: 'GET',
        dataType: 'JSON'
    });

    request.done(function (response) {
        $('#data__person--icon-gender').addClass(
            response.data.person.gender === 'HOMBRE' ? 'fa-person' : 'fa-person-dress'
        )
        $('#data__person--name').text(response.data.person.fullName)

        $.each(response.data.employee, function (key, value) {
            $(`#data__employee--${key}`).text(value)
        })

        $.each(response.data.corporateDistribution, function (key, value) {
            $(`#data__corporateDistribution--${key}`).text(value.id !== null ? value.fullName : '-')
        })

        let email = response.data.user.email,
            phoneExtension = response.data.user.phoneExtension

        $('#data__user--email').text(email)
        $('#data__user--phoneExtension').text(phoneExtension)

        email !== null ? $('#input__user--email').val(email) : null
        phoneExtension !== null ? $('#input__user--phoneExtension').val(phoneExtension) : null

        if (!email || !phoneExtension) {
            modal.show()
        }

        let clientsContainer = $('#div--clients-tags'),
            clientsLength = response.data.clients.length

        clientsContainer.html('')

        for (let i = 0; i < clientsLength; i++) {
            clientsContainer.append(
                `<span class="mx-1 my-1 badge rounded-pill bg-dark">${response.data.clients[i]}</span>`
            )
        }
    });

    request.fail(function (request) {
        let title,
            description,
            type

        if (!request.responseJSON.data) {
            title = request.responseJSON.title
            description = request.responseJSON.description
            type = request.responseJSON.type

            import('./helpers.min.js').then(module => module.buildToast(title, description, type))

            return
        }
    });
}

getUserInformation();

/**
 * --------------------------------------------------------------------------
 * Update user's information.
 * --------------------------------------------------------------------------
 */

const buttonsUpdate = document.querySelectorAll('.button__update--user-info');

buttonsUpdate.forEach(element => element.addEventListener('click', () => {
    modal.show();
}));

/**
 * --------------------------------------------------------------------------
 * Client side validation.
 * --------------------------------------------------------------------------
 */
import("./helpers.min.js").then(module => module.validateForm())

/**
 * --------------------------------------------------------------------------
 * Submit form.
 * --------------------------------------------------------------------------
 */
const form = $('#form__update--user-info'),
    buttonSubmit = $('#button__submit--user-info')

const buttonStates = {
    'start': 'Actualizar',
    'processing': '<i class="me-2 fa-solid fa-spinner fa-spin-pulse"></i>Procesando'
}

buttonSubmit.on('click', updateInfo)

function updateInfo() {
    let request = $.ajax({
        method: 'POST',
        url: 'Dashboard/updateUserContactData',
        data: form.serializeArray(),
        dataType: 'JSON',
        beforeSend: function () {
            buttonSubmit.html(buttonStates.processing).prop('disabled', true)
        }
    })

    let title,
        description,
        type

    request.done(function (response) {
        title = response.message.title
        description = response.message.description
        type = response.message.type

        import('./helpers.min.js').then(module => module.buildToast(title, description, type))

        getUserInformation()
    })

    request.fail(function (request) {
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

    request.always(function (response) {
        modal.hide()

        buttonSubmit.html(buttonStates.start).prop('disabled', false)
    })
}
