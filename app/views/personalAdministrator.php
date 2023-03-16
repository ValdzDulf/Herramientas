        <nav class="mb-5 navbar navbar-light opacity-sertec-20">
            <div class="container-fluid">
                <span class="fs-4">Personal Administrator</span>
            </div>
        </nav>
        <main class="container">
            <section class="mb-3 d-flex justify-content-end flex-wrap">
                <div class="btn-group" role="group">
                    <button id="button__update--catalog" type="button"
                            class="p-2 btn btn-info btn-sm text-white">
                        <i class="me-2 fa-solid fa-gears"></i>Update
                    </button>
                    <button id="button__download--log" type="button"
                            class="p-2 ms-2 btn btn-dark btn-sm text-white">
                        <i class="me-2 fa-solid fa-box-archive"></i>Log
                    </button>
                </div>
            </section>
            <section>
                <p class="fs-3">Person</p>
                <table id="table__data--person" class="w-100 table table-hover table-sm align-middle">
                    <thead>
                        <tr class="opacity-sertec-80 text-light">
                            <th class="align-middle text-center">Id</th>
                            <th class="align-middle text-center">First Surname</th>
                            <th class="align-middle text-center">Second Surname</th>
                            <th class="align-middle text-center">First Name</th>
                            <th class="align-middle text-center">Gender</th>
                            <th class="align-middle text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </section>
            <section>
                <p class="fs-3">Employee</p>
                <table id="table__data--employee" class="w-100 table table-hover table-sm align-middle">
                    <thead>
                        <tr class="opacity-sertec-80 text-light">
                            <th class="align-middle text-center">Person Id</th>
                            <th class="align-middle text-center">Job Code</th>
                            <th class="align-middle text-center">Tenured Director Code</th>
                            <th class="align-middle text-center">Director Code</th>
                            <th class="align-middle text-center">Manager Code</th>
                            <th class="align-middle text-center">Supervisor Code</th>
                            <th class="align-middle text-center">Ribbon</th>
                            <th class="align-middle text-center">Position</th>
                            <th class="align-middle text-center">Shift</th>
                            <th class="align-middle text-center">Branch</th>
                            <th class="align-middle text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </section>
        </main>

        <div class="position-absolute p-3 bottom-0 end-0 div__toast--custom-notification"></div>
