<!DOCTYPE html>
<html lang="<?= $ll_manager->user_language ?>">
  <head>
    <!-- Settings -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="<?= $router->generateLinkDomain() ?>">

    <!-- Information -->
    <title><?= $title ?></title>
    <meta name="author" content="<?= $author ?>">
    <meta name="description" content="<?= $description ?>">

    <!-- Icon -->
    <link rel="shortcut icon" href="<?= $router->generateLinkDomain() ?>/content/favicon.ico" type="image/x-icon">

    <!-- Style -->
    <link rel="stylesheet" href="<?= $router->generateLinkDomain() ?>/assets/css/main.css" type="text/css">

    <!-- Language -->
    <link rel="alternate" href="<?= $router->generateLinkAlternate() ?>" hreflang="en">
    <link rel="alternate" href="<?= $router->generateLinkAlternate('fr') ?>" hreflang="fr">
  </head>
  <body>
    <?= $content ?>
  </body>
</html>