'use strict'

/**
 * --------------------------------------------------------------------------
 * Client - Manager table.
 * --------------------------------------------------------------------------
 */
const clientManagerTable = $('#table__data--client-manager')

$.fn.dataTable.ext.errMode = 'none'

const clientManagerTableInstance = clientManagerTable.DataTable({
    ordering: false,
    processing: true,
    ajax: {
        url: 'ClientManagerAdministrator/getClientManagerRelationship',
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
        {data: 'clientId'},
        {data: 'client'},
        {data: 'managerId'},
        {data: 'manager'},
        {data: 'isActive'},
        {data: 'actions'}
    ],
    columnDefs: [
        {
            targets: [0, 1, 2, 3, 4],
            className: 'text-center'
        },
        {
            targets: 4,
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
            targets: 5,
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
}).on('error.dt', function (e, settings, techNote, message) {
    let errorMessage = `An error occurred while retrieving the data, notify the system administrator`
    console.log(message)

    $('.dataTables_empty').html(errorMessage)
})

clientManagerTable.on('page.dt', function () {
    $('html,body').animate({
        scrollTop: clientManagerTable.closest('body').offset().top
    }, 100)
})

/**
 * --------------------------------------------------------------------------
 * Update relationship status.
 * --------------------------------------------------------------------------
 */
clientManagerTableInstance.on('click', '.button__update--status', function () {
    let data = clientManagerTableInstance.row($(this).parents()).data();

    updateStatus(data.clientId, data.managerId);
});

function updateStatus(clientId, managerId) {
    let title,
        description,
        type

    let request = $.ajax({
        method: 'POST',
        url: 'ClientManagerAdministrator/updateStatus',
        data: {clientId: clientId, managerId: managerId},
        dataType: 'JSON'
    })

    request.done(function (response) {
        clientManagerTableInstance.ajax.reload()

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
 * Insert client manager relationship.
 * --------------------------------------------------------------------------
 */
const modalInsertClientManager = new bootstrap.Modal(
    document.getElementById('modal__insert--client-manager'), {}
)

document.getElementById('button__insert--client-manager').addEventListener(
    'click', () => {
        let managerSelector = $('#select__client-manager--manager-id')

        let title,
            description,
            type

        let request = $.ajax({
            method: 'GET',
            url: 'SelectorBuilder/getActiveManagers',
            dataType: 'JSON',
            beforeSend: function () {
                managerSelector.removeClass('is-invalid is-valid')
                managerSelector.next().remove()
                managerSelector.find('option').remove()

                $('.div--dynamic-select').empty()
            }
        })

        request.done(function (response) {
            response.data.forEach(function (element) {
                managerSelector.append($('<option>', {
                    'text': element.text,
                    'value': element.value,
                    'data-extra': element.extra
                }))
            })

            modalInsertClientManager.show()
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
            }
        })
    }
)

document.getElementById('select__client-manager--manager-id').addEventListener('change', () => {
    let selector = document.getElementById('select__client-manager--manager-id'),
        managerId = selector.options[selector.selectedIndex].value

    import('./helpers.min.js').then(module => module.buildSelector(
        selector, 'getClientsExcludingManager', 'client-id', 'Client Id', 'clientId', `managerId=${managerId}`
        )
    )
})

document.getElementById('button__submit--client-manager').addEventListener(
    'click', insertClientManager
)

function insertClientManager() {
    let form = $('#form__insert--client-manager'),
        buttonSubmit = $('#button__submit--client-manager'),
        buttonStates = {
            'start': 'Insert',
            'processing': '<i class="me-2 fa-solid fa-spinner fa-spin-pulse"></i>Procesando'
        }

    let title,
        description,
        type

    let request = $.ajax({
        method: 'POST',
        url: 'ClientManagerAdministrator/insertClientManagerRelationship',
        data: form.serializeArray(),
        dataType: 'JSON',
        beforeSend: function () {
            buttonSubmit.html(buttonStates.processing).prop('disabled', true)
        }
    })

    request.done(function (response) {
        clientManagerTableInstance.ajax.reload()

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
        modalInsertClientManager.hide()

        import('./helpers.min.js').then(module => module.buildToast(title, description, type))
    })
}
