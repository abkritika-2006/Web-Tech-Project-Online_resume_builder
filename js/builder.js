/* ─── Tab switching ───────────────────────────────────────── */
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const target = btn.dataset.tab;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-' + target).classList.add('active');
  });
});

/* ─── Word counter (summary) ──────────────────────────────── */
const summaryEl  = document.getElementById('summary');
const countEl    = document.getElementById('summary-count');
if (summaryEl && countEl) {
  function updateCount() {
    const words = summaryEl.value.trim().split(/\s+/).filter(Boolean).length;
    countEl.textContent = words + ' word' + (words !== 1 ? 's' : '');
    countEl.style.color = words > 80 ? '#e07070' : words > 50 ? '#70c09a' : 'var(--text-faint)';
  }
  summaryEl.addEventListener('input', updateCount);
  updateCount();
}

/* ─── Dynamic entry templates ─────────────────────────────── */
const entryTemplates = {
  experience: (i) => `
    <div class="entry-card" data-index="${i}">
      <button type="button" class="btn btn-danger btn-sm remove-btn" onclick="removeEntry(this, 'experience')">&#215;</button>
      <div class="form-row">
        <div class="form-group">
          <label>Job title</label>
          <input type="text" name="experience[${i}][title]" placeholder="Software Engineer" />
        </div>
        <div class="form-group">
          <label>Company</label>
          <input type="text" name="experience[${i}][company]" placeholder="Acme Inc." />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Start date</label>
          <input type="text" name="experience[${i}][start]" placeholder="Jan 2021" />
        </div>
        <div class="form-group">
          <label>End date</label>
          <input type="text" name="experience[${i}][end]" placeholder="Present" />
        </div>
      </div>
      <div class="form-group">
        <label>Location (optional)</label>
        <input type="text" name="experience[${i}][location]" placeholder="Remote / New York, NY" />
      </div>
      <div class="form-group">
        <label>Description &amp; achievements</label>
        <textarea name="experience[${i}][description]" placeholder="• Led a team of 4 engineers..."></textarea>
      </div>
    </div>`,

  education: (i) => `
    <div class="entry-card" data-index="${i}">
      <button type="button" class="btn btn-danger btn-sm remove-btn" onclick="removeEntry(this, 'education')">&#215;</button>
      <div class="form-row">
        <div class="form-group">
          <label>Degree / qualification</label>
          <input type="text" name="education[${i}][degree]" placeholder="B.Sc. Computer Science" />
        </div>
        <div class="form-group">
          <label>School / university</label>
          <input type="text" name="education[${i}][school]" placeholder="MIT" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Start year</label>
          <input type="text" name="education[${i}][start]" placeholder="2016" />
        </div>
        <div class="form-group">
          <label>End year</label>
          <input type="text" name="education[${i}][end]" placeholder="2020" />
        </div>
      </div>
      <div class="form-group">
        <label>Notes (optional)</label>
        <input type="text" name="education[${i}][notes]" placeholder="GPA 3.9 · Dean's List" />
      </div>
    </div>`
};

let entryCounters = { experience: 0, education: 0 };

function addEntry(type) {
  const container = document.getElementById(type + '-entries');
  const existing  = container.querySelectorAll('.entry-card').length;
  const idx       = existing + entryCounters[type]++;
  const div       = document.createElement('div');
  div.innerHTML   = entryTemplates[type](idx);
  container.appendChild(div.firstElementChild);
}

function removeEntry(btn, type) {
  btn.closest('.entry-card').remove();
  reindexEntries(type);
}

function reindexEntries(type) {
  const cards = document.querySelectorAll(`#${type}-entries .entry-card`);
  cards.forEach((card, i) => {
    card.dataset.index = i;
    card.querySelectorAll('[name]').forEach(el => {
      el.name = el.name.replace(/\[\d+\]/, `[${i}]`);
    });
  });
}

/* ─── Skills ──────────────────────────────────────────────── */
function getSkills() {
  return JSON.parse(document.getElementById('skills-data').value || '[]');
}

function saveSkills(arr) {
  document.getElementById('skills-data').value = JSON.stringify(arr);
}

function renderSkills() {
  const display = document.getElementById('skills-display');
  const skills = getSkills();

  display.innerHTML = skills.map(s => `
    <span class="skill-tag">
      ${escHtml(s)}
      <button
        type="button"
        onclick="removeSkill(this)"
        title="Remove"
      >&times;</button>
    </span>
  `).join('');
}
function addSkill() {
  const input = document.getElementById('skill-input');
  const raw = input.value.trim();
  if (!raw) return;

  const parts = raw.split(',').map(s => s.trim()).filter(Boolean);
  const skills = getSkills();

  parts.forEach(p => {
    if (!skills.includes(p)) skills.push(p);
  });

  saveSkills(skills);
  renderSkills();

  input.value = '';
  input.focus();

  // Save immediately
  saveResume();
}

function removeSkill(btn) {
  const skillName = btn.parentElement.textContent
    .replace('×', '')
    .trim();

  const skills = getSkills().filter(
    s => s.trim() !== skillName
  );

  saveSkills(skills);
  renderSkills();

  // Immediately save to database
  saveResume();
}

document.getElementById('skill-input')?.addEventListener('keydown', e => {
  if (e.key === 'Enter') { e.preventDefault(); addSkill(); }
});

/* ─── Form submit & save ──────────────────────────────────── */
const form       = document.getElementById('resume-form');
const statusEl   = document.getElementById('save-status');
const errorEl    = document.getElementById('save-error');
let   saveTimer  = null;

function showStatus(msg, isError = false) {
  const el = isError ? errorEl : statusEl;
  const hide = isError ? statusEl : errorEl;
  hide.style.display  = 'none';
  el.textContent      = msg;
  el.style.display    = 'flex';
  clearTimeout(el._timer);
  el._timer = setTimeout(() => { el.style.display = 'none'; }, 3500);
}

function normalizeUrl(url) {
  if (!url.trim()) return '';
  if (!/^https?:\/\//i.test(url)) {
    return 'https://' + url;
  }
  return url;
}

async function saveResume(e) {
  if (e) e.preventDefault();

  // Auto-fix URLs before saving
  const linkedin = document.getElementById('linkedin');
  const github   = document.getElementById('github');
  const website  = document.getElementById('website');

  if (linkedin && linkedin.value.trim()) {
    linkedin.value = normalizeUrl(linkedin.value);
  }

  if (github && github.value.trim()) {
    github.value = normalizeUrl(github.value);
  }

  if (website && website.value.trim()) {
    website.value = normalizeUrl(website.value);
  }

  const data = new FormData(form);

  try {
    const res = await fetch('php/save_resume.php', {
      method: 'POST',
      body: data
    });

    const json = await res.json();

    if (json.success) {
      showStatus('✓ ' + json.message);
    } else {
      showStatus(json.error || 'Something went wrong.', true);
    }
  } catch (err) {
    showStatus('Network error — changes not saved.', true);
  }
}

form.addEventListener('submit', saveResume);

// Autosave after 2 s of inactivity
form.addEventListener('input', () => {
  clearTimeout(saveTimer);
  saveTimer = setTimeout(() => saveResume(null), 2000);
});

/* ─── Helpers ─────────────────────────────────────────────── */
function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}
