'use strict'

/**
 * --------------------------------------------------------------------------
 * Manager table.
 * --------------------------------------------------------------------------
 */
const managerTable = $('#table__data--manager')

$.fn.dataTable.ext.errMode = 'throw'

const managerTableInstance = managerTable.DataTable({
    ordering: false,
    ajax: {
        url: 'ManagerAdministrator/getManagers',
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
        {data: 'jobCode'},
        {data: 'fullName'},
        {data: 'isActive'},
        {data: 'actions'}
    ],
    columnDefs: [
        {
            targets: [0, 1, 3],
            className: 'text-center'
        },
        {
            targets: 3,
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
            targets: 4,
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
                `
            }
        }
    ],
    language: {
        url: '/herramientas/config/datatables-language.json'
    },
    responsive: true
})

managerTable.on('page.dt', function () {
    $('html,body').animate({
        scrollTop: managerTable.closest('body').offset().top
    }, 100)
})

/**
 * --------------------------------------------------------------------------
 * Update manager status.
 * --------------------------------------------------------------------------
 */
managerTableInstance.on('click', '.button__update--status', function () {
    let data = managerTableInstance.row($(this).parents()).data();

    updateStatus(data.id, data.jobCode);
});

function updateStatus(userId, jobCode) {
    let title,
        description,
        type

    let request = $.ajax({
        method: 'POST',
        url: 'ManagerAdministrator/updateStatus',
        data: {id: userId, jobCode: jobCode},
        dataType: 'JSON'
    })

    request.done(function (response) {
        managerTableInstance.ajax.reload()

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
        import('./helpers.min.js').then(module => module.buildToast(title, description, type))
    })
}

/**
 * --------------------------------------------------------------------------
 * Insert manager.
 * --------------------------------------------------------------------------
 */
const modalInsertManager = new bootstrap.Modal(document.getElementById('modal__insert--manager'), {})

document.getElementById('button__insert--manager').addEventListener(
    'click', () => {
        modalInsertManager.show()
    }
)

document.getElementById('modal__insert--manager').addEventListener('show.bs.modal', () => {
    let elements = document.forms['form__insert--manager'].getElementsByTagName('input')

    for (let item of elements) {
        item.value = ''
        item.classList.remove('is-valid', 'is-invalid')

        let sibling = item.nextElementSibling

        if (sibling !== null) {
            sibling.remove()
        }
    }

    import('./helpers.min.js').then(module => module.validateForm())
})

document.getElementById('button__submit--manager').addEventListener(
    'click', insertManager
)

function insertManager() {
    let form = $('#form__insert--manager'),
        buttonSubmit = $('#button__submit--manager'),
        buttonStates = {
            'start': 'Insert',
            'processing': '<i class="me-2 fa-solid fa-spinner fa-spin-pulse"></i>Procesando'
        }

    let title,
        description,
        type

    let request = $.ajax({
        method: 'POST',
        url: 'ManagerAdministrator/insertManager',
        data: form.serializeArray(),
        dataType: 'JSON',
        beforeSend: function () {
            buttonSubmit.html(buttonStates.processing).prop('disabled', true)
        }
    })

    request.done(function (response) {
        managerTableInstance.ajax.reload()

        title = response.message.title
        description = response.message.description
        type = response.message.type

        modalInsertManager.hide()

        import('./helpers.min.js').then(module => module.buildToast(title, description, type))

        buttonSubmit.html(buttonStates.start).prop('disabled', false)
    })

    request.fail(function (request) {
        if (!request.hasOwnProperty('responseJSON')) {
            title = 'Request Execution'
            description = 'An error occurred while executing the request'
            type = 'danger'

            modalInsertManager.hide()

            import('./helpers.min.js').then(module => module.buildToast(title, description, type))

            buttonSubmit.html(buttonStates.start).prop('disabled', false)

            return;
        }

        if (!request.responseJSON.data) {
            title = request.responseJSON.title
            description = request.responseJSON.description
            type = request.responseJSON.type

            modalInsertManager.hide()

            import('./helpers.min.js').then(module => module.buildToast(title, description, type))

            buttonSubmit.html(buttonStates.start).prop('disabled', false)

            return;
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

        buttonSubmit.html(buttonStates.start).prop('disabled', false)
    })
}
