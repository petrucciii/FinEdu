<?php // view non necessaria, con la gestiona a aggetti anche una singola riga è restituita come istanza
?>


<h2><?= esc($title) ?></h2>


<?php if (! empty($language) && is_array($language)) :  
    ?>

    <?php 
        foreach ($language as $language_item): ?>
        <h3><?= esc($language_item) ?></h3>

    <?php endforeach ?>

<?php else: ?>

    <h3>No News</h3>

    <p>Unable to find any news for you.</p>

<?php endif ?>