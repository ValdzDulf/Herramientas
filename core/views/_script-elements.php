        <script src="<?php echo(RESOURCES_PATH); ?>popper-2.11.6/popper.min.js"></script>
        <script src="<?php echo(RESOURCES_PATH); ?>bootstrap-5.3.0/bootstrap.min.js"></script>
        <script src="<?php echo(RESOURCES_PATH); ?>jquery-3.6.3/jquery-3.6.3.min.js"></script>
        <script src="<?php echo(RESOURCES_PATH); ?>fontawesome-6.2.1/fontawesome.min.js"></script>
        <script src="<?php echo(RESOURCES_PATH); ?>datatables-1.13.1/datatables.min.js"></script>
<?php if (
    isset($useTimer) && $useTimer === 1
    && file_exists(PROJECT_PATH . '/app/assets/js/session-timer.min.js')
): ?>
        <script src="<?php echo(APP_RESOURCES_PATH); ?>js/_session-timer.min.js"></script>
<?php endif; ?>
<?php if (isset($jsFile) && file_exists(PROJECT_PATH . '/app/assets/js/' . $jsFile . '.js')): ?>
        <script src="<?php echo(APP_RESOURCES_PATH); ?>js/<?php echo($jsFile); ?>.js<?php if (isset($jsVersion)) {echo ($jsVersion);} ?>"></script>
<?php endif; ?>
    </body>
</html>
