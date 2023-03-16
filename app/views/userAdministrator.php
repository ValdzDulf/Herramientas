        <nav class="mb-5 navbar navbar-light opacity-sertec-20">
            <div class="container-fluid">
                <span class="fs-4">User Administrator</span>
            </div>
        </nav>
        <main class="container">
            <section>
                <table id="table__data--user" class="w-100 table table-hover table-sm align-middle">
                    <thead>
                        <tr class="opacity-sertec-80 text-light">
                            <th class="align-middle text-center">Id</th>
                            <th class="align-middle text-center">Profile</th>
                            <th class="align-middle text-center">Job Code</th>
                            <th class="align-middle text-center">Email</th>
                            <th class="align-middle text-center">Phone Extension</th>
                            <th class="align-middle text-center">Status</th>
                            <th class="align-middle text-center"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </section>
        </main>

        <div id="modal__update--user-profile" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="border-0 modal-content">
                    <div class="modal-header justify-content-center bg-primary text-white">
                        <h5 class="modal-title">Update Profile</h5>
                    </div>
                    <div class="modal-body">
                        <form id="form__update--user-profile">
                            <div class="mb-3">
                                <label for="input__user--job-code">
                                    User <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="jobCode" id="input__user--job-code"
                                       class="form-control" readonly required>
                            </div>

                            <div class="mb-3">
                                <label for="select__user--profile-id">
                                    Profile <span class="text-danger">*</span>
                                </label>
                                <select name="profileId" id="select__user--profile-id" class="form-select"
                                        required>
                                </select>
                            </div>

                            <p class="mb-1 form-text text-danger text-end">
                                <small>* Required fields</small>
                            </p>
                        </form>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" id="button__submit--user-profile"
                                class="btn btn-outline-primary">
                            Update
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="position-absolute p-3 bottom-0 end-0 div__toast--custom-notification"></div>
