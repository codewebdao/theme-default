<?php
http_response_code($statusCode ?? 500);
view_header(['layout' => $layout ?? 'errors']);
?>
<main class="container mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold">Error</h1>
    <p class="mt-2 text-gray-600"><?php echo e($message ?? 'Unknown error.'); ?></p>
</main>
<?php view_footer(); ?>

