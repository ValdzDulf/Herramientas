        <main class="vh-100 d-flex justify-content-center align-items-center bg-sertec-primary">
            <section class="container">
                <div class="row align-items-center justify-content-center">
                    <div class="col-12 col-lg-10">
                        <div class="border-0 card bg-transparent">
                            <div class="card-body p-3 p-lg-5">
                                <div class="row">
                                    <div class="col-12 col-md-7 align-self-center text-center">
                                        <img src="<?php echo(MULTIMEDIA_PATH); ?>sertec.png" alt="Logo SERTEC"
                                             class="img-fluid">
                                        <img src="<?php echo(MULTIMEDIA_PATH); ?>centros.png"
                                             alt="Centros SERTEC" class="img-fluid">
                                    </div>
                                    <div class="col-12 col-md-5 align-self-center">
                                        <p class="fs-5 text-center text-light">
                                            Sistema de Asignación de Cuentas
                                            <span id="popover--sign-in-info" class="ms-2 d-inline-block"
                                                  tabindex="0" data-bs-placement="bottom"
                                                  data-bs-toggle="popover" data-bs-trigger="hover focus">
                                                <i class="fa-regular fa-bell fa-shake text-info"
                                                   style="--fa-animation-duration: 4s;">
                                                </i>
                                            </span>
                                        </p>
                                        <form id="form--sign-in">
                                            <label for="input__sign-in--username"
                                                   class="form-label text-light">
                                                Clave
                                            </label>
                                            <div class="mb-3 input-group">
                                                <span class="border-0 input-group-text bg-secondary
                                                             text-light">
                                                    <i class="fa-solid fa-user-tie"></i>
                                                </span>
                                                <input type="text" name="username"
                                                       id="input__sign-in--username" class="form-control"
                                                       minlength="5" maxlength="5" pattern="[A-Z]{5}"
                                                       required>
                                            </div>

                                            <label for="input__sign-in--password"
                                                   class="form-label text-light">
                                                Contraseña
                                            </label>
                                            <div class="mb-3 input-group">
                                                <span class="border-0 input-group-text bg-secondary
                                                             text-light">
                                                    <i class="fa-solid fa-key"></i>
                                                </span>
                                                <input type="password" name="password"
                                                       id="input__sign-in--password" class="form-control"
                                                       autocomplete="on" required>
                                                <button class="btn btn-outline-info" type="button">
                                                    <i class="fa-solid fa-lock"></i>
                                                </button>
                                            </div>
                                        </form>
                                        <div class="d-grid gap-2 d-md-block mt-5 text-center">
                                            <button type="button" id="button__submit-form--sign-in"
                                                    class="btn btn-success">
                                                Iniciar Sesión
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <div class="position-absolute p-3 bottom-0 end-0 div__toast--custom-notification"></div>
