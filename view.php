<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <link href="alaska.css" rel="stylesheet" />
    <title>Billet simple pour l'Alaska - Home</title>
</head>
<body>
    <header>
        <h1>Billet simple pour l'Alaska</h1>
    </header>
    <?php foreach ($billets as $billet): ?>
    <article>
        <h2><?php echo $billet['billet_title'] ?></h2>
        <p><?php echo $billet['billet_content'] ?></p>
    </article>
    <?php endforeach ?>
    <footer class="footer">
        <strong>Billet simple pour l'Alaska</strong>, une histoire originale de Jean Forteroche.
    </footer>
</body>
</html>