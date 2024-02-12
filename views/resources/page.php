<!DOCTYPE html>
<html lang="<?= $t->user_lang ?>">
  <head>
    <!-- Settings -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="<?= $router->generateDomainLink() ?>">

    <!-- Information -->
    <title><?= $title ?></title>
    <meta name="author" content="<?= $author ?>">
    <meta name="description" content="<?= $description ?>">

    <!-- Icon -->
    <link rel="shortcut icon" href="<?= $router->generateDomainLink() ?>/content/favicon.ico" type="image/x-icon">

    <!-- Style -->
    <link rel="stylesheet" href="<?= $router->generateDomainLink() ?>/assets/css/main.css" type="text/css">

    <!-- Language -->
    <link rel="alternate" href="<?= $router->generateAltermateLink() ?>" hreflang="en">
    <link rel="alternate" href="<?= $router->generateAltermateLink('fr') ?>" hreflang="fr">
  </head>
  <body>
    <?= $content ?>
  </body>
</html>