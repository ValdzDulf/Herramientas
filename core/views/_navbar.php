        <!-- TODO: Remove true conditions at the end of the update. -->
        <nav class="navbar navbar-expand-xl navbar-dark bg-sertec-primary">
            <div class="container-fluid">
                <span class="navbar-brand">
                    <img src="<?=MULTIMEDIA_PATH?>mis.png" alt="logo MIS" id="img__logo--navbar">
                </span>
                <button type="button" class="navbar-toggler" <?=(isset($isBootstrap4) ? 'data-toggle="collapse"' : 'data-bs-toggle="collapse"')?>
                        <?=(isset($isBootstrap4) ? 'data-target="#navbar__navigation--links"' : 'data-bs-target="#navbar__navigation--links"')?> aria-expanded="false">
                    <span class="navbar-toggler-icon"></span>
                </button>
<?php if(isset($_SESSION['userKey'])): ?>
                <div class="collapse navbar-collapse" id="navbar__navigation--links">
                    <ul class="navbar-nav <?=(isset($isBootstrap4) ? 'mr-auto' : 'me-auto')?> text-white"><?=(isset($menu) ? $menu : '')?>

                    </ul>
                    <div class="d-flex flex-column flex-xl-row">
                        <a class="btn btn-link text-white text-decoration-none" href="Dashboard">
                            <i class="<?=(isset($isBootstrap4) ? 'mr-2' : 'me-2')?> fa-lg <?=(isset($isBootstrap4) ? 'far' : 'fa-regular')?> fa-id-badge"></i><?=$_SESSION['userKey']?>
                        </a>
                        <a href="Session/logout" class="btn btn-link text-white text-decoration-none">
                            <i class="<?=(isset($isBootstrap4) ? 'mr-2' : 'me-2')?> <?=(isset($isBootstrap4) ? 'fas fa-sign-out-alt' : 'fa-solid fa-arrow-right-from-bracket')?>"></i>Cerrar Sesión
                        </a>
                    </div>
                </div>
<?php endif ?>
            </div>
        </nav>
