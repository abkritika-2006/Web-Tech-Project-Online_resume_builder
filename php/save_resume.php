<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$user = currentUser();
$uid  = $user['id'];

// Sanitise scalar fields
function s(string $key): string {
    return trim($_POST[$key] ?? '');
}

$full_name = s('full_name');
$job_title = s('job_title');
$email     = s('email');
$phone     = s('phone');
$location  = s('location');
$linkedin  = s('linkedin');
$github    = s('github');
$website   = s('website');
$summary   = s('summary');

// Skills – already JSON-encoded from JS
$skillsRaw = s('skills');
$skills    = json_encode(json_decode($skillsRaw, true) ?: []);

// Experience entries
$expRaw = $_POST['experience'] ?? [];
$experience = [];
foreach ((array) $expRaw as $entry) {
    $experience[] = [
        'title'       => trim($entry['title']       ?? ''),
        'company'     => trim($entry['company']     ?? ''),
        'start'       => trim($entry['start']       ?? ''),
        'end'         => trim($entry['end']         ?? ''),
        'location'    => trim($entry['location']    ?? ''),
        'description' => trim($entry['description'] ?? ''),
    ];
}

// Education entries
$eduRaw   = $_POST['education'] ?? [];
$education = [];
foreach ((array) $eduRaw as $entry) {
    $education[] = [
        'degree' => trim($entry['degree'] ?? ''),
        'school' => trim($entry['school'] ?? ''),
        'start'  => trim($entry['start']  ?? ''),
        'end'    => trim($entry['end']    ?? ''),
        'notes'  => trim($entry['notes']  ?? ''),
    ];
}

// Check if resume exists for this user
$stmt = $pdo->prepare('SELECT id FROM resumes WHERE user_id = ?');
$stmt->execute([$uid]);
$existing = $stmt->fetch();

if ($existing) {
    $stmt = $pdo->prepare('
        UPDATE resumes SET
            full_name=?, job_title=?, email=?, phone=?, location=?,
            linkedin=?, github=?, website=?,
            summary=?, skills=?, experience=?, education=?
        WHERE user_id=?
    ');
    $stmt->execute([
        $full_name, $job_title, $email, $phone, $location,
        $linkedin, $github, $website,
        $summary, $skills,
        json_encode($experience), json_encode($education),
        $uid
    ]);
} else {
    $stmt = $pdo->prepare('
        INSERT INTO resumes
            (user_id, full_name, job_title, email, phone, location,
             linkedin, github, website, summary, skills, experience, education)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
    ');
    $stmt->execute([
        $uid,
        $full_name, $job_title, $email, $phone, $location,
        $linkedin, $github, $website,
        $summary, $skills,
        json_encode($experience), json_encode($education),
    ]);
}

echo json_encode(['success' => true, 'message' => 'Resume saved successfully.']);
