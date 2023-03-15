        <main class="container">
            <section class="my-4 row row-cols-1 row-cols-md-2 g-4">
                <aside class="col">
                    <div class="h-100 card">
                        <div class="d-flex flex-column align-items-center justify-content-center card-body">
                            <p class="display-1 text-center">
                                <i id="data__person--icon-gender" class="fa-solid"></i>
                            </p>
                            <p id="data__person--name" class="mb-1 h4 font-weight-bold text-center"></p>
                            <p id="data__employee--position" class="mb-1 text-secondary text-center"></p>
                            <p id="data__employee--branch" class="mb-1 text-secondary text-center"></p>
                        </div>
                    </div>
                </aside>
                <article class="col">
                    <div class="h-100 card">
                        <div class="card-body">
                            <dl class="mb-0 row">
                                <dt class="mb-2 col-12 col-lg-5 border-bottom">Fecha de Ingreso</dt>
                                <dd id="data__employee--startDate"
                                    class="mb-2 col-12 col-lg-7 border-bottom"></dd>

                                <dt class="mb-2 col-12 col-lg-5 border-bottom">Director Titular</dt>
                                <dd id="data__corporateDistribution--tenuredDirectorCode"
                                    class="mb-2 col-12 col-lg-7 border-bottom"></dd>

                                <dt class="mb-2 col-12 col-lg-5 border-bottom">Director</dt>
                                <dd id="data__corporateDistribution--directorCode"
                                    class="mb-2 col-12 col-lg-7 border-bottom"></dd>

                                <dt class="mb-2 col-12 col-lg-5 border-bottom">Gerente</dt>
                                <dd id="data__corporateDistribution--managerCode"
                                    class="mb-2 col-12 col-lg-7 border-bottom"></dd>

                                <dt class="mb-2 col-12 col-lg-5 border-bottom">Supervisor</dt>
                                <dd id="data__corporateDistribution--supervisorCode"
                                    class="mb-2 col-12 col-lg-7 border-bottom"></dd>

                                <dt class="mb-2 col-12 col-xl-5 border-bottom">Correo Electrónico</dt>
                                <dd class="mb-2 col-12 col-xl-7 border-bottom">
                                    <dl class="mb-0 row">
                                        <dt id="data__user--email" class="col-10 fw-normal"></dt>
                                        <dd class="col-2">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-success
                                                           button__update--user-info">
                                                <i class="fa-solid fa-pencil"></i>
                                            </button>
                                        </dd>
                                    </dl>
                                </dd>

                                <dt class="mb-2 col-12 col-lg-5">Extensión Telefónica</dt>
                                <dd class="mb-2 col-12 col-lg-7">
                                    <dl class="mb-0 row">
                                        <dt id="data__user--phoneExtension" class="col-10 fw-normal"></dt>
                                        <dd class="col-2">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-success
                                                           button__update--user-info">
                                                <i class="fa-solid fa-pencil"></i>
                                            </button>
                                        </dd>
                                    </dl>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </article>
            </section>
            <section class="row g-4">
                <aside class="col-12 col-md-6 col-lg-4 offset-md-6 offset-lg-8 text-md-center">
                    <p class="fs-5">Clientes Asignados</p>
                    <div class="py-3 container border" id="div--clients-tags">
                    </div>
                </aside>
            </section>
        </main>

        <div id="modal__update--user-info" class="modal fade" tabindex="-1" data-bs-backdrop="static"
             data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="border-0 modal-content">
                    <div class="modal-header justify-content-center bg-primary text-white">
                        <h5 class="modal-title">Actualización de Datos</h5>
                    </div>
                    <div class="modal-body">
                        <form id="form__update--user-info">
                            <div class="mb-3">
                                <label for="input__user--email" class="form-label">
                                    Correo Electrónico <span class="text-danger">*</span>
                                </label>
                                <input type="email" name="email" id="input__user--email" class="form-control"
                                       pattern="[\w.-]+@sertec\.com\.mx" required>
                            </div>
                            <div class="mb-3">
                                <label for="input__user--phoneExtension" class="form-label">
                                    Extensión Telefónica <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="phoneExtension"
                                       id="input__user--phoneExtension" class="form-control" required>
                            </div>

                            <p class="mb-1 form-text text-danger text-end">
                                <small>* Campos Obligatorios</small>
                            </p>
                        </form>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" id="button__submit--user-info"
                                class="btn btn-outline-primary">
                            Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="position-absolute p-3 bottom-0 end-0 div__toast--custom-notification"></div>
