<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireLogin();

$user = currentUser();

// Load existing resume
$stmt = $pdo->prepare('SELECT * FROM resumes WHERE user_id = ?');
$stmt->execute([$user['id']]);
$resume = $stmt->fetch();

// Defaults
$r = $resume ?: [];
$experience = json_decode($r['experience'] ?? '[]', true) ?: [];
$education  = json_decode($r['education']  ?? '[]', true) ?: [];
$skills     = json_decode($r['skills']     ?? '[]', true) ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Resume Builder — ResumeForge</title>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
  <a href="dashboard.php" class="navbar-brand">Resume<span>Forge</span></a>
  <div class="navbar-links">
    <a href="dashboard.php" class="nav-link">Dashboard</a>
    <a href="preview.php" class="nav-link">Preview</a>
    <a href="logout.php" class="btn btn-outline btn-sm">Sign out</a>
  </div>
</nav>

<div class="container" style="padding-top: 40px; padding-bottom: 80px;">
  <div style="margin-bottom: 32px;">
    <h1>Resume Builder</h1>
    <p style="margin-top: 8px;">Fill in your details below. All changes save automatically.</p>
  </div>

  <!-- Save status -->
  <div id="save-status" style="display:none;" class="alert alert-success" role="status"></div>
  <div id="save-error"  style="display:none;" class="alert alert-error"  role="alert"></div>

  <!-- Tabs -->
  <div class="tabs" role="tablist">
    <button class="tab-btn active" data-tab="personal"    role="tab">Personal info</button>
    <button class="tab-btn"        data-tab="summary"     role="tab">Summary</button>
    <button class="tab-btn"        data-tab="experience"  role="tab">Experience</button>
    <button class="tab-btn"        data-tab="education"   role="tab">Education</button>
    <button class="tab-btn"        data-tab="skills"      role="tab">Skills</button>
    <button class="tab-btn"        data-tab="links"       role="tab">Links</button>
  </div>

  <form id="resume-form">

    <!-- ─── Personal Info ─────────────────────────── -->
    <div class="tab-panel active" id="tab-personal">
      <div class="form-row">
        <div class="form-group">
          <label for="full_name">Full name</label>
          <input type="text" id="full_name" name="full_name"
                 value="<?= htmlspecialchars($r['full_name'] ?? '') ?>"
                 placeholder="Jane Doe" />
        </div>
        <div class="form-group">
          <label for="job_title">Job title / desired role</label>
          <input type="text" id="job_title" name="job_title"
                 value="<?= htmlspecialchars($r['job_title'] ?? '') ?>"
                 placeholder="Senior Product Designer" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email"
                 value="<?= htmlspecialchars($r['email'] ?? '') ?>"
                 placeholder="you@example.com" />
        </div>
        <div class="form-group">
          <label for="phone">Phone</label>
          <input type="tel" id="phone" name="phone"
                 value="<?= htmlspecialchars($r['phone'] ?? '') ?>"
                 placeholder="+1 (555) 000-0000" />
        </div>
      </div>
      <div class="form-group">
        <label for="location">Location</label>
        <input type="text" id="location" name="location"
               value="<?= htmlspecialchars($r['location'] ?? '') ?>"
               placeholder="San Francisco, CA" />
      </div>
    </div>

    <!-- ─── Summary ───────────────────────────────── -->
    <div class="tab-panel" id="tab-summary">
      <div class="form-group">
        <label for="summary">Professional summary</label>
        <textarea id="summary" name="summary" rows="6"
                  placeholder="A brief 2–4 sentence overview of who you are, your experience, and what you bring to the table."><?= htmlspecialchars($r['summary'] ?? '') ?></textarea>
        <p class="form-hint">Aim for 50–80 words. This is the first thing recruiters read.</p>
      </div>
      <div id="summary-count" style="font-size:0.82rem;color:var(--text-faint);margin-top:-12px;margin-bottom:16px;">0 words</div>
    </div>

    <!-- ─── Experience ────────────────────────────── -->
    <div class="tab-panel" id="tab-experience">
      <div id="experience-entries">
        <?php foreach ($experience as $i => $exp): ?>
        <div class="entry-card" data-index="<?= $i ?>">
          <button type="button" class="btn btn-danger btn-sm remove-btn" onclick="removeEntry(this, 'experience')">&#215;</button>
          <div class="form-row">
            <div class="form-group">
              <label>Job title</label>
              <input type="text" name="experience[<?= $i ?>][title]"
                     value="<?= htmlspecialchars($exp['title'] ?? '') ?>"
                     placeholder="Software Engineer" />
            </div>
            <div class="form-group">
              <label>Company</label>
              <input type="text" name="experience[<?= $i ?>][company]"
                     value="<?= htmlspecialchars($exp['company'] ?? '') ?>"
                     placeholder="Acme Inc." />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Start date</label>
              <input type="text" name="experience[<?= $i ?>][start]"
                     value="<?= htmlspecialchars($exp['start'] ?? '') ?>"
                     placeholder="Jan 2021" />
            </div>
            <div class="form-group">
              <label>End date</label>
              <input type="text" name="experience[<?= $i ?>][end]"
                     value="<?= htmlspecialchars($exp['end'] ?? '') ?>"
                     placeholder="Present" />
            </div>
          </div>
          <div class="form-group">
            <label>Location (optional)</label>
            <input type="text" name="experience[<?= $i ?>][location]"
                   value="<?= htmlspecialchars($exp['location'] ?? '') ?>"
                   placeholder="Remote / New York, NY" />
          </div>
          <div class="form-group">
            <label>Description &amp; achievements</label>
            <textarea name="experience[<?= $i ?>][description]"
                      placeholder="• Led a team of 4 engineers to ship X feature, resulting in 20% uplift in retention."><?= htmlspecialchars($exp['description'] ?? '') ?></textarea>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="btn btn-outline" onclick="addEntry('experience')">
        &#43; Add experience
      </button>
    </div>

    <!-- ─── Education ─────────────────────────────── -->
    <div class="tab-panel" id="tab-education">
      <div id="education-entries">
        <?php foreach ($education as $i => $edu): ?>
        <div class="entry-card" data-index="<?= $i ?>">
          <button type="button" class="btn btn-danger btn-sm remove-btn" onclick="removeEntry(this, 'education')">&#215;</button>
          <div class="form-row">
            <div class="form-group">
              <label>Degree / qualification</label>
              <input type="text" name="education[<?= $i ?>][degree]"
                     value="<?= htmlspecialchars($edu['degree'] ?? '') ?>"
                     placeholder="B.Sc. Computer Science" />
            </div>
            <div class="form-group">
              <label>School / university</label>
              <input type="text" name="education[<?= $i ?>][school]"
                     value="<?= htmlspecialchars($edu['school'] ?? '') ?>"
                     placeholder="MIT" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Start year</label>
              <input type="text" name="education[<?= $i ?>][start]"
                     value="<?= htmlspecialchars($edu['start'] ?? '') ?>"
                     placeholder="2016" />
            </div>
            <div class="form-group">
              <label>End year</label>
              <input type="text" name="education[<?= $i ?>][end]"
                     value="<?= htmlspecialchars($edu['end'] ?? '') ?>"
                     placeholder="2020" />
            </div>
          </div>
          <div class="form-group">
            <label>Notes (optional)</label>
            <input type="text" name="education[<?= $i ?>][notes]"
                   value="<?= htmlspecialchars($edu['notes'] ?? '') ?>"
                   placeholder="GPA 3.9 · Dean's List" />
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="btn btn-outline" onclick="addEntry('education')">
        &#43; Add education
      </button>
    </div>

    <!-- ─── Skills ────────────────────────────────── -->
    <div class="tab-panel" id="tab-skills">
      <label>Skills</label>
      <div class="skills-container" id="skills-display">
        <?php foreach ($skills as $skill): ?>
          <span class="skill-tag">
            <?= htmlspecialchars($skill) ?>
            <button type="button" onclick="removeSkill(this)" title="Remove">&#215;</button>
          </span>
        <?php endforeach; ?>
      </div>
      <input type="hidden" id="skills-data" name="skills" value="<?= htmlspecialchars(json_encode($skills)) ?>" />
      <div class="skill-input-row">
        <input type="text" id="skill-input" placeholder="e.g. JavaScript, Figma, Project Management" />
        <button type="button" class="btn btn-outline" onclick="addSkill()">Add</button>
      </div>
      <p class="form-hint mt-8">Press Enter or click Add. Separate multiple with commas.</p>
    </div>

    <!-- ─── Links ─────────────────────────────────── -->
    <div class="tab-panel" id="tab-links">
      <div class="form-group">
        <label for="linkedin">LinkedIn URL</label>
        <input type="url" id="linkedin" name="linkedin"
               value="<?= htmlspecialchars($r['linkedin'] ?? '') ?>"
               placeholder="https://linkedin.com/in/yourname" />
      </div>
      <div class="form-group">
        <label for="github">GitHub URL</label>
        <input type="url" id="github" name="github"
               value="<?= htmlspecialchars($r['github'] ?? '') ?>"
               placeholder="https://github.com/yourname" />
      </div>
      <div class="form-group">
        <label for="website">Portfolio / website</label>
        <input type="url" id="website" name="website"
               value="<?= htmlspecialchars($r['website'] ?? '') ?>"
               placeholder="https://yoursite.com" />
      </div>
    </div>

    <!-- Save button -->
    <div style="margin-top: 32px; display: flex; gap: 12px; align-items: center;">
      <button type="submit" class="btn btn-primary btn-lg">&#10003; Save resume</button>
      <a href="preview.php" class="btn btn-outline btn-lg">&#128065; Preview</a>
    </div>

  </form>
</div>

<script src="js/builder.js"></script>
</body>
</html>
