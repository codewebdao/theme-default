<?php
/**
 * View file for Restaurant Schema
 * Data is passed to the view through the $schemaData variable
 */
?>
<script type="application/ld+json">
<?= json_encode($schemaData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?>
</script>
