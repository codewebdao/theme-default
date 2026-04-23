<?php
/**
 * @var string $type
 * @var string $message
 * @var string|null $title
 */
$type = $type ?? 'info';
$icon = '';
$color = '';
$border = '';
$close = '';
switch ($type) {
  case 'success':
    $icon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>';
    $color = 'bg-green-100 text-green-800';
    $border = 'border border-green-300';
    $close = 'text-green-700 hover:text-green-900';
    break;
  case 'error':
    $icon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>';
    $color = 'bg-red-100 text-red-800';
    $border = 'border border-red-300';
    $close = 'text-red-700 hover:text-red-900';
    break;
  case 'warning':
    $icon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12A9 9 0 11 3 12a9 9 0 0118 0z" /></svg>';
    $color = 'bg-yellow-100 text-yellow-800';
    $border = 'border border-yellow-300';
    $close = 'text-yellow-700 hover:text-yellow-900';
    break;
  default:
    $icon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01" /></svg>';
    $color = 'bg-blue-100 text-blue-800';
    $border = 'border border-blue-300';
    $close = 'text-blue-700 hover:text-blue-900';
    break;
}
?>
<div x-data="{ show: true }" x-show="show" x-transition.opacity class="flex items-center gap-3 <?= $color ?> <?= $border ?> px-4 py-3 rounded-lg mb-4 shadow">
  <?= $icon ?>
  <div class="flex-1">
    <?php if (!empty($title)): ?>
      <div class="font-semibold mb-0.5"><?= htmlspecialchars($title) ?></div>
    <?php endif; ?>
    <span><?= ($message) ?></span>
  </div>
  <button @click="show = false" class="ml-2 <?= $close ?> focus:outline-none">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
    <span class="sr-only">Close</span>
  </button>
</div>