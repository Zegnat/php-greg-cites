<?php

declare(strict_types=1);

require '../vendor/autoload.php';

use RenanBr\BibTexParser\Listener;
use RenanBr\BibTexParser\Parser;
use Twig\Loader\FilesystemLoader;
use Twig\Environment as Twig;


$loader = new FilesystemLoader('../templates');
$twig = new Twig($loader);

$templates = array_map(
    function ($item): string {
        return substr($item, 0, -5);
    },
    array_filter(
        \scandir('../templates', \SCANDIR_SORT_ASCENDING),
        function ($item): bool {
            return $item[0] !== '.';
        }
    )
);

$out = '';
$post = '';
$template = 'APA';
if (isset($_POST['bibtex'])) {
    $post = $_POST['bibtex'];
    if (isset($_POST['template']) && in_array($_POST['template'], $templates)) {
        $template = $_POST['template'];
    }
    $parser = new Parser();
    $listener = new Listener();
    $parser->addListener($listener);
    $parser->parseString($post);
    $out = $twig->render($template . '.html', ['articles' => $listener->export()]);
}

?><!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>BibTeX to microformats2 enhanced HTML</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <form method="post">
      <label for="bibtex">BibTeX</label>
      <textarea id="bibtex" name="bibtex" autofocus><?= htmlspecialchars($post, ENT_NOQUOTES) ?></textarea>
      <label for="template">Template to use</label>
      <select id="template" name="template">
<?php foreach ($templates as $option) { ?>
        <option<?= $option === $template ? ' selected' : ''?>><?= $option ?></option>
<?php } ?>
      </select>
      <button type="submit">Convert</button>
    </form>
    <label for="snippet">HTML</label>
    <textarea id="snippet" readonly><?= htmlspecialchars($out, ENT_NOQUOTES) ?></textarea>
    <p>Preview</p>
    <div id="preview"><?= $out ?? '' ?></div>
  </body>
</html>
