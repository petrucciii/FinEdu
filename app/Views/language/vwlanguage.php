<h2><?= esc($title) ?></h2>


<?php if (! empty($language) && is_array($language)) :  
    ?>

    <?php  // dataset come oggetto
    //var_dump($title);var_dump($language); die;
        foreach ($language as $language_item) 
   /* :?>     
   //     <h3><?= esc($language_item->language_id).' '.esc($language_item->name) ?></h3>
    <?php 
   */
        {    echo('<h3>'.$language_item->language_id.' '.$language_item->name.'</h3>');
        }
    //endforeach 
    ?>

<?php else: ?>

    <h3>Nessuna lingua</h3>

    <p>Non sono state trovate lingue con i criteri specificati</p>

<?php endif ?>