<?php
require_once 'config/db.php';
require_once 'auth.php';
require_login();
header('Cache-Control: no-store, no-cache, must-revalidate');
$session_pid = (int)$_SESSION['profile_id'];

// Student profile (only the logged-in user's profile)
$profile = null;
$stmt = mysqli_prepare($conn, "SELECT * FROM profile WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $session_pid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($result && mysqli_num_rows($result) > 0) {
    $profile = mysqli_fetch_assoc($result);
}

// Education (owner-scoped)
$educations = [];
$stmt = mysqli_prepare($conn, "SELECT * FROM education WHERE profile_id = ? ORDER BY start_year DESC");
mysqli_stmt_bind_param($stmt, 'i', $session_pid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $educations[] = $row;
    }
}

/*
TODO FOR STUDENTS
1. Create table: skill
2. Create table: training
3. Insert your own data in phpMyAdmin
4. Write SQL SELECT statements below

Suggested fields:
- skill(id, profile_id, skill_name, skill_level)
- training(id, profile_id, course_name, organization, training_year)
*/

$skills = [];
$trainings = [];

if ($profile) {
    $profile_id = intval($profile['id']);

    $sql = "SELECT * FROM skill WHERE profile_id = $profile_id";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $skills[] = $row;
        }
    }

    $sql = "SELECT * FROM training WHERE profile_id = $profile_id ORDER BY training_year DESC";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $trainings[] = $row;
        }
    }
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $profile ? e($profile['full_name']) : 'My Resume'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Noto+Sans+Thai:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">

    <header class="hero card">
        <img class="avatar" src="assets/images/<?php echo $profile ? e($profile['photo']) : 'student.jpg'; ?>" alt="Profile Photo">
        <div>
            <p class="eyebrow">Resume</p>
            <h1><?php echo $profile ? e($profile['full_name']) : 'Your Name'; ?></h1>
            <p class="headline"><?php echo $profile ? e($profile['headline']) : 'Student Resume Website'; ?></p>
            <?php if ($profile): ?>
                <ul class="contact">
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                        <?php echo e($profile['email']); ?>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="2" width="12" height="20" rx="2"/><path d="M11 18h2"/></svg>
                        <?php echo e($profile['phone']); ?>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                        <?php echo e($profile['address']); ?>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </header>

    <section class="card">
        <h2>About Me</h2>
        <p class="about"><?php echo $profile ? nl2br(e($profile['about_me'])) : 'Add your profile data in phpMyAdmin.'; ?></p>
    </section>

    <section class="card">
        <h2>Education</h2>
        <?php if (count($educations) > 0): ?>
            <div class="timeline">
                <?php foreach ($educations as $edu): ?>
                    <div class="item">
                        <h3><?php echo e($edu['degree']); ?></h3>
                        <p class="meta"><?php echo e($edu['institution']); ?></p>
                        <span class="year"><?php echo e($edu['start_year']); ?> – <?php echo e($edu['end_year']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="empty">No education data found.</p>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Skills</h2>
        <?php if (count($skills) > 0): ?>
            <div class="skills">
                <?php foreach ($skills as $skill): ?>
                    <div class="skill-badge">
                        <strong><?php echo e($skill['skill_name']); ?></strong>
                        <span><?php echo e($skill['skill_level']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="empty">No skill data found.</p>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Training / Certificates</h2>
        <?php if (count($trainings) > 0): ?>
            <div class="cert-grid">
                <?php foreach ($trainings as $training): ?>
                    <?php $cert_src = 'assets/images/' . (!empty($training['certificates']) ? $training['certificates'] : 'Certificate.jpg'); ?>
                    <a class="cert"
                       href="<?php echo e($cert_src); ?>"
                       target="_blank"
                       data-title="<?php echo e($training['course_name']); ?>"
                       data-org="<?php echo e($training['organization']); ?>"
                       data-year="<?php echo e($training['training_year']); ?>">
                        <div class="cert-thumb">
                            <img class="certificates"
                                 src="<?php echo e($cert_src); ?>"
                                 alt="Certificate for <?php echo e($training['course_name']); ?>"
                                 loading="lazy">
                            <span class="cert-zoom">View full version</span>
                        </div>
                        <div class="cert-body">
                            <h3><?php echo e($training['course_name']); ?></h3>
                            <p class="meta"><?php echo e($training['organization']); ?></p>
                            <span class="year"><?php echo e($training['training_year']); ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="empty">No training data found.</p>
        <?php endif; ?>
    </section>

    <footer>
        Database Lab <span>·</span> Personal Resume Website with PHP + MySQL
    </footer>

</div>

<!-- Certificate viewer (lightbox) -->
<dialog class="lightbox" id="lightbox" aria-label="Certificate viewer">
    <button class="lb-btn lb-close" type="button" aria-label="Close">&times;</button>
    <button class="lb-btn lb-prev" type="button" aria-label="Previous">&#8249;</button>
    <figure>
        <img id="lb-img" src="" alt="">
        <figcaption>
            <h3 id="lb-title"></h3>
            <p class="meta" id="lb-meta"></p>
            <span class="lb-count" id="lb-count"></span>
        </figcaption>
    </figure>
    <button class="lb-btn lb-next" type="button" aria-label="Next">&#8250;</button>
</dialog>

<script>
(function () {
    const cards = Array.from(document.querySelectorAll('.cert'));
    const box = document.getElementById('lightbox');
    if (!cards.length || !box || typeof box.showModal !== 'function') return;

    const img = document.getElementById('lb-img');
    const title = document.getElementById('lb-title');
    const meta = document.getElementById('lb-meta');
    const count = document.getElementById('lb-count');
    let current = 0;

    function show(i) {
        current = (i + cards.length) % cards.length;
        const c = cards[current];
        img.src = c.getAttribute('href');
        img.alt = 'Certificate for ' + c.dataset.title;
        title.textContent = c.dataset.title;
        meta.textContent = c.dataset.org + ' · ' + c.dataset.year;
        count.textContent = cards.length > 1 ? (current + 1) + ' / ' + cards.length : '';
    }

    cards.forEach(function (c, i) {
        c.addEventListener('click', function (ev) {
            ev.preventDefault();
            show(i);
            box.showModal();
            document.body.classList.add('no-scroll');
        });
    });

    box.querySelector('.lb-close').addEventListener('click', function () { box.close(); });
    box.querySelector('.lb-prev').addEventListener('click', function () { show(current - 1); });
    box.querySelector('.lb-next').addEventListener('click', function () { show(current + 1); });
    box.classList.toggle('single', cards.length < 2);

    // Click outside the image closes the viewer
    box.addEventListener('click', function (ev) {
        if (ev.target === box) box.close();
    });
    box.addEventListener('close', function () {
        document.body.classList.remove('no-scroll');
    });
    document.addEventListener('keydown', function (ev) {
        if (!box.open) return;
        if (ev.key === 'ArrowLeft') show(current - 1);
        if (ev.key === 'ArrowRight') show(current + 1);
    });
})();
</script>
</body>
</html>
<?php mysqli_close($conn); ?>