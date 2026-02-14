<h2><?= esc($title) ?></h2>

<?php if ($language) :
?>

    <h3> Lingua cancellata correttamente</h3>

<?php else : ?>

    <h3>Nessuna lingua cancellata</h3>

    <p>Non sono state trovate lingue con i criteri specificati</p>

<?php endif ?>