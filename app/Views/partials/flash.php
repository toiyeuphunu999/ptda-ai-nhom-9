<?php if (!empty($successMessage)): ?>
    <div class="flash-message flash-success" role="status"><span class="material-symbols-outlined" aria-hidden="true">check_circle</span><?= \App\Helpers\ViewHelper::escape((string)$successMessage) ?></div>
<?php endif; ?>
<?php if (!empty($failureMessage)): ?>
    <div class="flash-message flash-error" role="alert"><span class="material-symbols-outlined" aria-hidden="true">error</span><?= \App\Helpers\ViewHelper::escape((string)$failureMessage) ?></div>
<?php endif; ?>
