'use strict'

/**
 * --------------------------------------------------------------------------
 * Person table.
 * --------------------------------------------------------------------------
 */
const personTable = $('#table__data--person')

$.fn.dataTable.ext.errMode = 'throw'

const personTableInstance = personTable.DataTable({
    ordering: false,
    ajax: {
        url: 'PersonalAdministrator/getPersons',
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
        {data: 'firstSurname'},
        {data: 'secondSurname'},
        {data: 'firstName'},
        {data: 'gender'},
        {data: 'isActive'}
    ],
    columnDefs: [
        {
            targets: [0, 4, 5],
            className: 'text-center'
        },
        {
            targets: 4,
            data: null,
            render: function (data, type, row) {
                return `
                    ${row.gender === 'HOMBRE'
                    ? '<i class="fa-solid fa-person" style="color: var(--bs-blue)"></i>'
                    : '<i class="fa-solid fa-person-dress" style="color: var(--bs-pink)"></i>'}
                `
            }
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
        }
    ],
    language: {
        url: '/herramientas/config/datatables-language.json'
    },
    responsive: true
})

personTable.on('page.dt', function () {
    $('html,body').animate({
        scrollTop: personTable.closest('section').offset().top
    }, 100)
})

/**
 * --------------------------------------------------------------------------
 * Employee table.
 * --------------------------------------------------------------------------
 */
const employeeTable = $('#table__data--employee')

const employeeTableInstance = employeeTable.DataTable({
    ordering: false,
    processing: true,
    ajax: {
        url: 'PersonalAdministrator/getEmployees',
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
        {data: 'personId'},
        {data: 'jobCode'},
        {data: 'tenuredDirectorCode'},
        {data: 'directorCode'},
        {data: 'managerCode'},
        {data: 'supervisorCode'},
        {data: 'colorRibbon'},
        {data: 'position'},
        {data: 'shift'},
        {data: 'branch'},
        {data: 'isActive'}
    ],
    columnDefs: [
        {
            targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
            className: 'text-center'
        },
        {
            targets: 2,
            data: null,
            render: function (data, type, row) {
                return row.tenuredDirectorCode === '' ? '-' : row.tenuredDirectorCode
            }
        },
        {
            targets: 3,
            data: null,
            render: function (data, type, row) {
                return row.directorCode === '' ? '-' : row.directorCode
            }
        },
        {
            targets: 4,
            data: null,
            render: function (data, type, row) {
                return row.managerCode === '' ? '-' : row.managerCode
            }
        },
        {
            targets: 5,
            data: null,
            render: function (data, type, row) {
                return row.supervisorCode === '' ? '-' : row.supervisorCode
            }
        },
        {
            targets: 6,
            data: null,
            render: function (data, type, row) {
                return `<i class='fa-solid fa-award
                           ribbon-${row.colorRibbon} ${row.colorRibbon === 'white' ? 'bg-secondary' : ''}'></i>`
            }
        },
        {
            targets: 10,
            data: null,
            render: function (data, type, row) {
                return `
                    <span class="badge bg-${row.isActive === '1' ? 'success' : 'dark'}">
                        ${row.isActive === '1' ? 'Active' : 'Inactive'}
                    </span>
                `;
            }
        }
    ],
    language: {
        url: '/herramientas/config/datatables-language.json'
    },
    responsive: true
})

employeeTable.on('page.dt', function () {
    $('html,body').animate({
        scrollTop: employeeTable.closest('section').offset().top
    }, 100)
})

/**
 * --------------------------------------------------------------------------
 * Update catalog.
 * --------------------------------------------------------------------------
 */

const buttonUpdate = $('#button__update--catalog')

const buttonStates = {
    'start': '<i class="me-2 fa-solid fa-gears"></i>Update',
    'processing': '<i class="me-2 fa-solid fa-spinner fa-spin-pulse"></i>Procesando'
}

buttonUpdate.on('click', updateCatalog)

function updateCatalog() {
    let title,
        description,
        type

    let request = $.ajax({
        method: 'POST',
        url: 'Staff/updateStaffCatalog/manually',
        dataType: 'JSON',
        beforeSend: function () {
            buttonUpdate.html(buttonStates.processing).prop('disabled', true)
        }
    })

    request.done(function (response) {
        personTableInstance.ajax.reload()
        employeeTableInstance.ajax.reload()
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
        }
    })

    request.always(function () {
        buttonUpdate.html(buttonStates.start).prop('disabled', false)

        import('./helpers.min.js').then(module => module.buildToast(title, description, type))
    })
}

/**
 * --------------------------------------------------------------------------
 * Log button.
 * --------------------------------------------------------------------------
 */
document.getElementById('button__download--log').addEventListener('click', () => {
    let temporaryElement = document.createElement('a')
    temporaryElement.href = window.location.origin + '/herramientas/log/staff/log.txt'
    temporaryElement.download = 'staff_log.txt'

    document.body.appendChild(temporaryElement)
    temporaryElement.click()
    document.body.removeChild(temporaryElement)
})
