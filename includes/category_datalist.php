<?php
// includes/category_datalist.php - suggestions for the Category input (create/edit forms)
// Expects $allCategories (from get_all_categories()) to be set by the including page.
?>
<datalist id="categoryOptions">
    <?php foreach ($allCategories as $cat): ?>
        <option value="<?= htmlspecialchars($cat["name"]) ?>"></option>
    <?php endforeach; ?>
</datalist>
