<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireLogin();

$user = currentUser();

// Load existing resume data
$stmt = $pdo->prepare('SELECT * FROM resumes WHERE user_id = ?');
$stmt->execute([$user['id']]);
$resume = $stmt->fetch();

$hasResume     = !empty($resume);
$completion    = 0;
$completedSteps = 0;

if ($hasResume) {
    $fields = ['full_name','job_title','email','phone','summary','skills','experience','education'];
    foreach ($fields as $f) {
        if (!empty($resume[$f])) $completedSteps++;
    }
    $completion = round(($completedSteps / count($fields)) * 100);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard — ResumeForge</title>
  <link rel="stylesheet" href="css/style.css" />
  <style>
    .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-top: 32px; }
    .stat-card { background: var(--bg2); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; }
    .stat-value { font-family: var(--font-display); font-size: 2rem; color: var(--text); }
    .stat-label { font-size: 0.82rem; color: var(--text-muted); margin-top: 4px; text-transform: uppercase; letter-spacing: 0.05em; }
    .progress-track { height: 6px; background: var(--bg3); border-radius: 3px; margin-top: 10px; }
    .progress-fill  { height: 100%; background: var(--accent); border-radius: 3px; transition: width 0.6s ease; }
    .cta-row { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 24px; }
    .quick-tip { background: var(--accent-dim); border: 1px solid rgba(201,169,110,0.15); border-radius: var(--radius-lg); padding: 20px 24px; margin-top: 24px; display: flex; gap: 16px; align-items: flex-start; }
    .tip-icon { font-size: 1.4rem; flex-shrink: 0; }
    .tip-title { font-weight: 500; font-size: 0.92rem; color: var(--accent); }
    .tip-body  { font-size: 0.85rem; color: var(--text-muted); margin-top: 4px; }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
  <a href="dashboard.php" class="navbar-brand">Resume<span>Forge</span></a>
  <div class="navbar-links">
    <a href="builder.php" class="nav-link">Builder</a>
    <a href="preview.php" class="nav-link">Preview</a>
    <a href="logout.php" class="btn btn-outline btn-sm">Sign out</a>
  </div>
</nav>

<div class="container">
  <div class="dashboard-hero">
    <h1>Welcome back, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?></h1>
    <p style="margin-top:8px;">Here's how your resume is looking. Keep it fresh!</p>

    <!-- Stats -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-value"><?= $completion ?>%</div>
        <div class="stat-label">Resume complete</div>
        <div class="progress-track">
          <div class="progress-fill" style="width:<?= $completion ?>%"></div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-value"><?= $completedSteps ?><span style="font-size:1rem;color:var(--text-muted)">/8</span></div>
        <div class="stat-label">Sections filled</div>
      </div>
      <div class="stat-card">
        <div class="stat-value" id="word-count">—</div>
        <div class="stat-label">Words written</div>
      </div>
    </div>

    <!-- CTA -->
    <div class="cta-row">
      <a href="builder.php" class="btn btn-primary btn-lg">
        <?= $hasResume ? '&#9998; Edit resume' : '&#43; Start building' ?>
      </a>
      <?php if ($hasResume): ?>
        <a href="preview.php" class="btn btn-outline btn-lg">&#128065; Preview &amp; Download</a>
      <?php endif; ?>
    </div>

    <!-- Quick tip -->
    <div class="quick-tip">
      <div class="tip-icon">&#128161;</div>
      <div>
        <div class="tip-title">Pro tip</div>
        <div class="tip-body">Tailor your summary for each role you apply to. Recruiters spend an average of 7 seconds on the first scan — make yours count.</div>
      </div>
    </div>
  </div>
</div>

<script>
// Rough word count from saved data
const summary = <?= json_encode($resume['summary'] ?? '') ?>;
const experience = <?= json_encode($resume['experience'] ?? '') ?>;
const words = [summary, experience].join(' ').split(/\s+/).filter(Boolean).length;
document.getElementById('word-count').textContent = words || '0';
</script>
</body>
</html>
