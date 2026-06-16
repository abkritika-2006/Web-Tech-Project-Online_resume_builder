<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireLogin();

$user = currentUser();

$stmt = $pdo->prepare('SELECT * FROM resumes WHERE user_id = ?');
$stmt->execute([$user['id']]);
$resume = $stmt->fetch();

if (!$resume) {
    header('Location: builder.php');
    exit;
}

$experience = json_decode($resume['experience'] ?? '[]', true) ?: [];
$education  = json_decode($resume['education']  ?? '[]', true) ?: [];
$skills     = json_decode($resume['skills']     ?? '[]', true) ?: [];

function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function nl2p(string $text): string {
    $lines = array_filter(array_map('trim', explode("\n", $text)));
    if (empty($lines)) return '';
    if (count($lines) === 1) return '<p class="resume-entry-desc">' . h($lines[0]) . '</p>';
    $html = '<ul style="padding-left:1.2rem;margin-top:8px;">';
    foreach ($lines as $line) {
        $line = ltrim($line, '•- ');
        $html .= '<li style="font-size:0.88rem;color:#444;margin-bottom:4px;line-height:1.6;">' . h($line) . '</li>';
    }
    return $html . '</ul>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= h($resume['full_name'] ?? 'Resume') ?> — ResumeForge</title>
  <link rel="stylesheet" href="css/style.css" />
  <style>
    body { background: var(--bg); }
    .preview-page { padding: 40px 20px 80px; }
    .toolbar { display: flex; align-items: center; justify-content: space-between; max-width: 794px; margin: 0 auto 24px; }
    .toolbar-title { font-family: var(--font-display); color: var(--text); font-size: 1.1rem; }
    @media print {
      .toolbar { display: none; }
      body { background: #fff; }
      .preview-page { padding: 0; }
    }
  </style>
</head>
<body>

<!-- Hidden navbar on print -->
<nav class="navbar no-print">
  <a href="dashboard.php" class="navbar-brand">Resume<span>Forge</span></a>
  <div class="navbar-links">
    <a href="builder.php" class="nav-link">&#9998; Edit</a>
    <a href="logout.php" class="btn btn-outline btn-sm">Sign out</a>
  </div>
</nav>

<div class="preview-page">

  <!-- Toolbar -->
  <div class="toolbar no-print">
    <span class="toolbar-title">Resume preview</span>
    <div style="display:flex;gap:10px;">
      <a href="builder.php" class="btn btn-outline btn-sm">&#9998; Edit</a>
      <button class="btn btn-primary btn-sm" onclick="window.print()">&#128438; Download / Print PDF</button>
    </div>
  </div>

  <!-- Resume Document -->
  <div class="resume-preview-wrapper">

    <!-- Header -->
    <div class="resume-header-bar">
      <div class="resume-name"><?= h($resume['full_name'] ?? '') ?></div>
      <?php if (!empty($resume['job_title'])): ?>
        <div class="resume-role"><?= h($resume['job_title']) ?></div>
      <?php endif; ?>

      <div class="resume-contacts">
        <?php if (!empty($resume['email'])): ?>
          <span><?= h($resume['email']) ?></span>
        <?php endif; ?>
        <?php if (!empty($resume['phone'])): ?>
          <span><?= h($resume['phone']) ?></span>
        <?php endif; ?>
        <?php if (!empty($resume['location'])): ?>
          <span><?= h($resume['location']) ?></span>
        <?php endif; ?>
        <?php if (!empty($resume['linkedin'])): ?>
          <a href="<?= h($resume['linkedin']) ?>" target="_blank">LinkedIn</a>
        <?php endif; ?>
        <?php if (!empty($resume['github'])): ?>
          <a href="<?= h($resume['github']) ?>" target="_blank">GitHub</a>
        <?php endif; ?>
        <?php if (!empty($resume['website'])): ?>
          <a href="<?= h($resume['website']) ?>" target="_blank">Portfolio</a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Summary -->
    <?php if (!empty($resume['summary'])): ?>
    <div class="resume-section">
      <div class="resume-section-title">Summary</div>
      <p class="resume-summary"><?= h($resume['summary']) ?></p>
    </div>
    <?php endif; ?>

    <!-- Experience -->
    <?php if (!empty($experience)): ?>
    <div class="resume-section">
      <div class="resume-section-title">Experience</div>
      <?php foreach ($experience as $exp): ?>
        <?php if (empty($exp['title']) && empty($exp['company'])) continue; ?>
        <div class="resume-entry">
          <div class="resume-entry-header">
            <div class="resume-entry-title"><?= h($exp['title'] ?? '') ?></div>
            <div class="resume-entry-dates">
              <?= h($exp['start'] ?? '') ?>
              <?php if (!empty($exp['end'])): ?> — <?= h($exp['end']) ?><?php endif; ?>
            </div>
          </div>
          <div class="resume-entry-sub">
            <?= h($exp['company'] ?? '') ?>
            <?php if (!empty($exp['location'])): ?> &middot; <?= h($exp['location']) ?><?php endif; ?>
          </div>
          <?php if (!empty($exp['description'])): ?>
            <?= nl2p($exp['description']) ?>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Education -->
    <?php if (!empty($education)): ?>
    <div class="resume-section">
      <div class="resume-section-title">Education</div>
      <?php foreach ($education as $edu): ?>
        <?php if (empty($edu['degree']) && empty($edu['school'])) continue; ?>
        <div class="resume-entry">
          <div class="resume-entry-header">
            <div class="resume-entry-title"><?= h($edu['degree'] ?? '') ?></div>
            <div class="resume-entry-dates">
              <?= h($edu['start'] ?? '') ?>
              <?php if (!empty($edu['end'])): ?> — <?= h($edu['end']) ?><?php endif; ?>
            </div>
          </div>
          <div class="resume-entry-sub"><?= h($edu['school'] ?? '') ?></div>
          <?php if (!empty($edu['notes'])): ?>
            <div class="resume-entry-desc"><?= h($edu['notes']) ?></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Skills -->
    <?php if (!empty($skills)): ?>
    <div class="resume-section">
      <div class="resume-section-title">Skills</div>
      <div class="resume-skills-grid">
        <?php foreach ($skills as $skill): ?>
          <span class="resume-skill-chip"><?= h($skill) ?></span>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div><!-- /resume-preview-wrapper -->
</div>

</body>
</html>
