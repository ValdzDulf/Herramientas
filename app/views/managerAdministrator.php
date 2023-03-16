        <nav class="mb-5 navbar navbar-light opacity-sertec-20">
            <div class="container-fluid">
                <span class="fs-4">Manager Administrator</span>
            </div>
        </nav>
        <main class="container">
            <section class="mb-3 d-flex justify-content-end flex-wrap">
                <div class="btn-group" role="group">
                    <button id="button__insert--manager" type="button"
                            class="p-2 btn btn-primary btn-sm text-white">
                        <i class="me-2 fa-solid fa-user-plus"></i>Insert
                    </button>
                </div>
            </section>
            <section>
                <table id="table__data--manager" class="w-100 table table-hover table-sm align-middle">
                    <thead>
                        <tr class="opacity-sertec-80 text-light">
                            <th class="align-middle text-center">Id</th>
                            <th class="align-middle text-center">Job Code</th>
                            <th class="align-middle text-center">Name</th>
                            <th class="align-middle text-center">Status</th>
                            <th class="align-middle text-center"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </section>
        </main>

        <div id="modal__insert--manager" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="border-0 modal-content">
                    <div class="modal-header justify-content-center bg-primary text-white">
                        <h5 class="modal-title">Register Manager</h5>
                    </div>
                    <div class="modal-body">
                        <form name="form__insert--manager" id="form__insert--manager">
                            <div class="mb-3">
                                <label for="input__manager--job-code">
                                    Job Code <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="jobCode" id="input__manager--job-code"
                                       class="form-control" minlength="5" maxlength="5" pattern="[A-Z]{5}"
                                       required>
                            </div>
                            <p class="mb-1 form-text text-danger text-end">
                                <small>* Required fields</small>
                            </p>
                        </form>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" id="button__submit--manager"
                                class="btn btn-outline-primary">
                            Insert
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="position-absolute p-3 bottom-0 end-0 div__toast--custom-notification"></div>
