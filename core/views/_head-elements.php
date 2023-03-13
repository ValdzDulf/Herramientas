<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="description" content="Internal system for the execution of alternative collection methods">
    <meta name="author"
          content="co-authored by Erick Pulido, Diego Valentín, Alberto Contreras, Gerardo Carrizosa">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php echo(isset($pageTitle) ? $pageTitle : 'Medios Alternos de Cobranza'); ?></title>
    <link rel="icon" type="image/x-icon" href="<?php echo(MULTIMEDIA_PATH); ?>favicon.ico">

    <link href="<?php echo(RESOURCES_PATH); ?>bootstrap-5.3.0/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo(RESOURCES_PATH); ?>datatables-1.13.1/datatables.min.css" rel="stylesheet">

    <?php if (file_exists(PROJECT_PATH . '/app/assets/css/app.min.css')) : ?>
        <link href="<?php echo(APP_RESOURCES_PATH); ?>css/app.min.css" rel="stylesheet">
    <?php endif; ?>
</head>
<body>
