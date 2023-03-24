        <nav class="mb-5 navbar navbar-light opacity-sertec-20">
            <div class="container-fluid">
                <span class="fs-4">Client - Manager Administrator</span>
            </div>
        </nav>

        <main class="container">
            <section class="mb-3 d-flex justify-content-end flex-wrap">
                <div class="btn-group" role="group">
                    <button id="button__insert--client-manager" type="button"
                            class="p-2 btn btn-primary btn-sm text-white">
                        <i class="me-2 fa-solid fa-user-plus"></i>Insert
                    </button>
                </div>
            </section>
            <section>
                <table id="table__data--client-manager" class="w-100 table table-hover table-sm align-middle">
                    <thead>
                        <tr class="opacity-sertec-80 text-light">
                            <th class="align-middle text-center">Client Id</th>
                            <th class="align-middle text-center">Client</th>
                            <th class="align-middle text-center">Manager Id</th>
                            <th class="align-middle text-center">Manager</th>
                            <th class="align-middle text-center">Status</th>
                            <th class="align-middle text-center"></th>
                        </tr>
                    </thead>
                </table>
            </section>
        </main>

        <div id="modal__insert--client-manager" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="border-0 modal-content">
                    <div class="modal-header justify-content-center bg-primary text-white">
                        <h5 class="modal-title">Adds Client Manager Relationship</h5>
                    </div>
                    <div class="modal-body">
                        <form name="form__insert--client-manager" id="form__insert--client-manager">
                            <div class="mb-3">
                                <label for="select__client-manager--manager-id">
                                    Manager <span class="text-danger">*</span>
                                </label>
                                <select name="managerId" id="select__client-manager--manager-id"
                                        class="form-select" required>
                                </select>
                            </div>

                            <div class="div--dynamic-select"></div>

                            <p class="mb-1 form-text text-danger text-end">
                                <small>* Required fields</small>
                            </p>
                        </form>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" id="button__submit--client-manager"
                                class="btn btn-outline-primary">
                            Insert
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="position-absolute p-3 bottom-0 end-0 div__toast--custom-notification"></div>
