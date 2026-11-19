<div class="container info-hero">
    <p class="eyebrow"><?= htmlspecialchars($infoEyebrow ?? 'Thông tin', ENT_QUOTES, 'UTF-8') ?></p>
    <h1><?= htmlspecialchars($infoHeading ?? ($title ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="lead"><?= htmlspecialchars($infoLead ?? '', ENT_QUOTES, 'UTF-8') ?></p>
</div>
