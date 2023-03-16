'use strict'

/**
 * --------------------------------------------------------------------------
 * User table.
 * --------------------------------------------------------------------------
 */
const userTable = $('#table__data--user')

$.fn.dataTable.ext.errMode = 'throw'

const userTableInstance = userTable.DataTable({
    ordering: false,
    ajax: {
        url: 'UserAdministrator/getUsers',
        type: 'GET'
    },
    dom: 'Bfrtip',
    buttons: [
        {
            extend: 'pageLength',
            text: '<i class="fa-solid fa-ellipsis-vertical"></i>',
            titleAttr: 'Rows per Page',
            className: 'btn-dark'
        },
        {
            text: '<i class="fa-solid fa-arrows-rotate"></i>',
            titleAttr: 'Sync',
            action: function (e, dt) {
                dt.ajax.reload(null, false);
            },
            className: 'btn-dark'
        },
        {
            text: '<i class="me-2 fa-solid fa-file-csv"></i>',
            titleAttr: 'Export CSV',
            extend: 'csv',
            charset: 'UTF-8',
            bom: true,
            className: 'btn-dark'
        }
    ],
    orderClasses: false,
    columns: [
        {data: 'id'},
        {data: 'descriptiveName'},
        {data: 'jobCode'},
        {data: 'email'},
        {data: 'phoneExtension'},
        {data: 'isActive'},
        {data: 'actions'}
    ],
    columnDefs: [
        {
            targets: [0, 1, 2, 4, 5, 6],
            className: 'text-center'
        },
        {
            targets: 5,
            data: null,
            render: function (data, type, row) {
                return `
                    <span class="badge bg-${row.isActive === '1' ? 'success' : 'dark'}">
                        ${row.isActive === '1' ? 'Active' : 'Inactive'}
                    </span>
                `;
            }
        },
        {
            targets: 6,
            data: null,
            render: function (data, type, row) {
                let title = row.isActive === '1' ? 'Disabled' : 'Enabled',
                    textColor = row.isActive === '1' ? 'text-success' : 'text-dark',
                    icon = row.isActive === '1' ? 'on' : 'off';

                return `
                    <button type="button" class="mx-1 btn btn-sm btn-link badge button__update--status"
                            title="${title}">
                        <i class="${textColor} fa-solid fa-toggle-${icon} fa-lg"></i>
                    </button>
                    
                    ${row.isActive === '1' ? `
                        <button type="button" class="mx-1 btn btn-sm btn-link badge button__update--profile"
                                title="Update Profile">
                            <i class="fa-solid fa-user-pen text-primary"></i>
                        </button>
                                        
                        ${row.wrongAttempts >= 10 ?
                        `<button type="button" class="mx-1 btn btn-sm btn-link badge
                                     button__unlock--wrong-attempts" title="Unlock Wrong Attempts">
                                <i class="fa-solid fa-unlock text-warning"></i>
                            </button>`
                        : ''
                    }
                    
                        ${row.inactiveDays >= 15 ?
                        `<button type="button" class="mx-1 btn btn-sm btn-link badge
                                     button__unlock--inactive-days" title="Unlock Inactive Days">
                                <i class="fa-regular fa-calendar-minus text-danger"></i>
                            </button>`
                        : ''
                    }`
                    : ''
                }
                `
            }
        }
    ],
    language: {
        url: '/herramientas/config/datatables-language.json'
    },
    responsive: true
})

userTable.on('page.dt', function () {
    $('html,body').animate({
        scrollTop: userTable.closest('body').offset().top
    }, 100)
})

/**
 * --------------------------------------------------------------------------
 * Update user status.
 * --------------------------------------------------------------------------
 */
userTableInstance.on('click', '.button__update--status', function () {
    let data = userTableInstance.row($(this).parents()).data();

    updateStatus(data.id, data.jobCode);
});

function updateStatus(userId, jobCode) {
    let title,
        description,
        type

    let request = $.ajax({
        method: 'POST',
        url: 'UserAdministrator/updateStatus',
        data: {id: userId, jobCode: jobCode},
        dataType: 'JSON'
    })

    request.done(function (response) {
        userTableInstance.ajax.reload()

        title = response.message.title
        description = response.message.description
        type = response.message.type
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

        let errors = ''

        $.each(request.responseJSON.data.validation, function (name, state) {
            errors += state.message !== null ? `${state.message} <br>` : ''
        })

        title = request.responseJSON.message.title
        description = errors
        type = request.responseJSON.message.type
    })

    request.always(function () {
        import('./helpers.min.js').then(module => module.buildToast(title, description, type))
    })
}

/**
 * --------------------------------------------------------------------------
 * Update user's profile.
 * --------------------------------------------------------------------------
 */

const modalUpdateProfile = new bootstrap.Modal(document.getElementById('modal__update--user-profile'), {})

userTableInstance.on('click', '.button__update--profile', function () {
    let profileSelector = $('#select__user--profile-id'),
        data = userTableInstance.row($(this).parents()).data()

    let title,
        description,
        type

    let request = $.ajax({
        method: 'GET',
        url: 'SelectorBuilder/getActiveProfiles',
        dataType: 'JSON',
        beforeSend: function () {
            profileSelector.removeClass('is-invalid is-valid')
            profileSelector.next().remove()
            profileSelector.find('option').remove()

        }
    })

    request.done(function (response) {
        console.log(response)

        import('./helpers.min.js').then(module => module.validateForm(0))

        response.data.forEach(function (element) {
            profileSelector.append($('<option>', {
                'text': element.text,
                'value': element.value,
                'data-extra': element.extra
            }))
        })

        $('#input__user--job-code').val(data.jobCode)

        $(`#select__user--profile-id option[value=${data.profileId}]`).attr('selected', 'selected')

        modalUpdateProfile.show();
    })

    request.fail(function (request) {
        console.log(request)

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
        }
    })
})


$('#button__submit--user-profile').on('click', function () {
    updateProfile()
})

function updateProfile() {
    let form = $('#form__update--user-profile'),
        buttonSubmit = $('#button__submit-form--update-user-profile'),
        buttonStates = {
            'start': 'Update',
            'processing': '<i class="me-2 fa-solid fa-spinner fa-spin-pulse"></i>Procesando'
        }

    let title,
        description,
        type

    let request = $.ajax({
        method: 'POST',
        url: 'UserAdministrator/updateProfile',
        data: form.serializeArray(),
        dataType: 'JSON',
        beforeSend: function () {
            buttonSubmit.html(buttonStates.processing).prop('disabled', true)
        }
    })

    request.done(function (response) {
        userTableInstance.ajax.reload()

        title = response.message.title
        description = response.message.description
        type = response.message.type
    })

    request.fail(function (request) {
        if (!request.hasOwnProperty('responseJSON')) {
            title = 'Request Execution'
            description = 'An error occurred while executing the request'
            type = 'danger'

            return
        }

        if (!request.responseJSON.data) {
            title = request.responseJSON.title
            description = request.responseJSON.description
            type = request.responseJSON.type

            return
        }

        let errors = ''

        $.each(request.responseJSON.data.validation, function (name, state) {
            errors += state.message !== null ? `${state.message} <br>` : ''
        })

        title = request.responseJSON.message.title
        description = errors
        type = request.responseJSON.message.type
    })

    request.always(function () {
        buttonSubmit.html(buttonStates.start).prop('disabled', false)
        modalUpdateProfile.hide()

        import('./helpers.min.js').then(module => module.buildToast(title, description, type))
    })
}

/**
 * --------------------------------------------------------------------------
 * Unlock user.
 * --------------------------------------------------------------------------
 */
userTableInstance.on('click', '.button__unlock--wrong-attempts', function () {
    let data = userTableInstance.row($(this).parents()).data();

    unlockUser(data.jobCode, data.lastSuccessAccessDate, 'wrongAttempt');
});

userTableInstance.on('click', '.button__unlock--inactive-days', function () {
    let data = userTableInstance.row($(this).parents()).data();

    unlockUser(data.jobCode, data.lastSuccessAccessDate, 'inactiveDays');
});

function unlockUser(jobCode, lastSuccessAccessDate, lockingType) {
    let title = null,
        description = null,
        type = null;

    let request = $.ajax({
        url: 'UserAdministrator/unlockUser',
        method: 'POST',
        data: {jobCode: jobCode, lastSuccessAccessDate: lastSuccessAccessDate, type: lockingType},
        dataType: 'JSON'
    })

    request.done(function (response) {
        title = response.message.title
        description = response.message.description
        type = response.message.type
    })

    request.fail(function (request) {
        if (!request.hasOwnProperty('responseJSON')) {
            title = 'Request Execution'
            description = 'An error occurred while executing the request'
            type = 'danger'

            return
        }

        if (!request.responseJSON.data) {
            title = request.responseJSON.title
            description = request.responseJSON.description
            type = request.responseJSON.type

            return
        }

        let errors = ''

        $.each(request.responseJSON.data.validation, function (name, state) {
            errors += state.message !== null ? `${state.message} <br>` : ''
        })

        title = request.responseJSON.message.title
        description = errors
        type = request.responseJSON.message.type
    })

    request.always(function () {
        userTableInstance.ajax.reload();

        import('./helpers.min.js').then(module => module.buildToast(title, description, type))
    })
}
