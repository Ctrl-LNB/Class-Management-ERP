<?php
// api.php — JSON API. All client mutations flow through here.
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/data.php';

header('X-Content-Type-Options: nosniff');

$rawBody = file_get_contents('php://input');
$input = json_decode($rawBody ?: '', true);
if (!is_array($input)) {
    $input = $_POST;
}

$action = s($input['action'] ?? ($_GET['action'] ?? ''));

if ($action === '') {
    jsonResponse(['ok' => false, 'error' => 'No action specified.'], 400);
}

$state = loadData();

/** Send back {ok:true, state, isAdmin} after a successful mutation/read. */
function respondWithState(array $state): void {
    jsonResponse(['ok' => true, 'state' => publicData($state), 'isAdmin' => isAdmin()]);
}

/** Helper to upload faculty images -> uploads/faculty */
function handleFacultyUpload(string $facultyId): string {
    if (!isset($_FILES['imageFile']) || $_FILES['imageFile']['error'] !== UPLOAD_ERR_OK) {
        return '';
    }
    $tmpName = $_FILES['imageFile']['tmp_name'];
    $ext = strtolower(pathinfo($_FILES['imageFile']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
        return '';
    }
    $targetDir = __DIR__ . '/uploads/faculty';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    $fileName = $facultyId . '.' . $ext;
    $targetPath = $targetDir . '/' . $fileName;
    if (move_uploaded_file($tmpName, $targetPath)) {
        return 'uploads/faculty/' . $fileName;
    }
    return '';
}

/** Helper to upload brand/asset images -> uploads/assets */
function handleBrandUpload(): string {
    if (!isset($_FILES['brandImageFile']) || $_FILES['brandImageFile']['error'] !== UPLOAD_ERR_OK) {
        return '';
    }
    $tmpName = $_FILES['brandImageFile']['tmp_name'];
    $ext = strtolower(pathinfo($_FILES['brandImageFile']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true)) {
        return '';
    }
    $targetDir = __DIR__ . '/uploads/assets';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    $fileName = 'brand_logo_' . time() . '.' . $ext;
    $targetPath = $targetDir . '/' . $fileName;
    if (move_uploaded_file($tmpName, $targetPath)) {
        return 'uploads/assets/' . $fileName;
    }
    return '';
}

/** Helper to upload notice resources -> uploads/notices */
function handleNoticeUpload(string $noticeId): string {
    if (!isset($_FILES['noticeFile']) || $_FILES['noticeFile']['error'] !== UPLOAD_ERR_OK) {
        return '';
    }
    $tmpName = $_FILES['noticeFile']['tmp_name'];
    $ext = strtolower(pathinfo($_FILES['noticeFile']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'zip', 'txt'];
    if (!in_array($ext, $allowed, true)) {
        return '';
    }
    $targetDir = __DIR__ . '/uploads/notices';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    $fileName = $noticeId . '_' . time() . '.' . $ext;
    $targetPath = $targetDir . '/' . $fileName;
    if (move_uploaded_file($tmpName, $targetPath)) {
        return 'uploads/notices/' . $fileName;
    }
    return '';
}

switch ($action) {

    /* ---------------- AUTH ---------------- */

    case 'getState':
        respondWithState($state);
        break;

    case 'login': {
        $password = s($input['password'] ?? '');
        if ($password !== '' && password_verify($password, $state['meta']['adminPasswordHash'])) {
            $_SESSION['isAdmin'] = true;
            respondWithState($state);
        } else {
            jsonResponse(['ok' => false, 'error' => 'Incorrect password.'], 401);
        }
        break;
    }

    case 'logout':
        $_SESSION['isAdmin'] = false;
        respondWithState($state);
        break;

    case 'changePassword': {
        requireAdmin();
        $oldPass = s($input['oldPassword'] ?? '');
        $newPass = s($input['newPassword'] ?? '');
        $confirmPass = s($input['confirmPassword'] ?? '');

        if ($oldPass === '' || $newPass === '' || $confirmPass === '') {
            jsonResponse(['ok' => false, 'error' => 'Please fill in all password fields.'], 400);
        }
        if (!password_verify($oldPass, $state['meta']['adminPasswordHash'])) {
            jsonResponse(['ok' => false, 'error' => 'Previous password is incorrect.'], 400);
        }
        if (strlen($newPass) < 4) {
            jsonResponse(['ok' => false, 'error' => 'New password must be at least 4 characters long.'], 400);
        }
        if ($newPass !== $confirmPass) {
            jsonResponse(['ok' => false, 'error' => 'New passwords do not match.'], 400);
        }
        $state['meta']['adminPasswordHash'] = password_hash($newPass, PASSWORD_DEFAULT);
        saveData($state);
        respondWithState($state);
        break;
    }

    /* ---------------- META / SETTINGS ---------------- */

    case 'saveMeta': {
        requireAdmin();
        $state['meta']['className'] = s($input['className'] ?? $state['meta']['className']);
        $state['meta']['semester'] = s($input['semester'] ?? $state['meta']['semester']);

        $uploadedAssetPath = handleBrandUpload();
        if ($uploadedAssetPath !== '') {
            $state['meta']['brandImageUrl'] = $uploadedAssetPath;
        } else {
            $brandImage = s($input['brandImageUrl'] ?? '');
            if ($brandImage !== '') {
                $state['meta']['brandImageUrl'] = saveDataUriIfPresent($brandImage, 'assets', 'brand_logo');
            }
        }

        saveData($state);
        respondWithState($state);
        break;
    }

    case 'importData': {
        requireAdmin();
        $incoming = $input['data'] ?? null;
        if (!is_array($incoming)) {
            jsonResponse(['ok' => false, 'error' => 'Invalid database file.'], 400);
        }
        if (empty($incoming['meta']['adminPasswordHash'])) {
            $incoming['meta']['adminPasswordHash'] = $state['meta']['adminPasswordHash'];
        }
        $state = normalizeData($incoming);
        saveData($state);
        respondWithState($state);
        break;
    }

    /* ---------------- NOTICES ---------------- */

    case 'addNotice': {
        requireAdmin();
        $title = s($input['title'] ?? '');
        if ($title === '') {
            jsonResponse(['ok' => false, 'error' => 'Please fill in notice title.'], 400);
        }
        $noticeId = makeId('n');
        $resourcePath = handleNoticeUpload($noticeId);

        $state['notices'][] = [
            'id' => $noticeId,
            'title' => $title,
            'note' => s($input['note'] ?? ''),
            'link' => s($input['link'] ?? ''),
            'resource' => $resourcePath,
            'date' => gmdate('Y-m-d H:i'),
            'isPinned' => true,
        ];
        saveData($state);
        respondWithState($state);
        break;
    }

    case 'editNotice': {
        requireAdmin();
        $id = s($input['id'] ?? '');
        $title = s($input['title'] ?? '');
        if ($id === '' || $title === '') {
            jsonResponse(['ok' => false, 'error' => 'Please fill in notice title.'], 400);
        }
        foreach ($state['notices'] as &$n) {
            if ($n['id'] === $id) {
                $n['title'] = $title;
                $n['note'] = s($input['note'] ?? '');
                $n['link'] = s($input['link'] ?? '');
                $resourcePath = handleNoticeUpload($id);
                if ($resourcePath !== '') {
                    $n['resource'] = $resourcePath;
                }
                break;
            }
        }
        unset($n);
        saveData($state);
        respondWithState($state);
        break;
    }

    case 'togglePinNotice': {
        requireAdmin();
        $id = s($input['id'] ?? '');
        foreach ($state['notices'] as &$n) {
            if ($n['id'] === $id) {
                $n['isPinned'] = ($n['isPinned'] ?? true) === false ? true : false;
            }
        }
        unset($n);
        saveData($state);
        respondWithState($state);
        break;
    }

    case 'deleteNotice': {
        requireAdmin();
        $id = s($input['id'] ?? '');
        $state['notices'] = array_values(array_filter($state['notices'], fn($n) => $n['id'] !== $id));
        saveData($state);
        respondWithState($state);
        break;
    }

    /* ---------------- CLASS ROUTINE ---------------- */

    case 'addPeriod': {
        requireAdmin();
        $day = s($input['day'] ?? '');
        $start = s($input['start'] ?? '');
        $end = s($input['end'] ?? '');
        $subject = s($input['subject'] ?? '');
        if (!in_array($day, DAYS, true) || $start === '' || $end === '' || $subject === '') {
            jsonResponse(['ok' => false, 'error' => 'Please fill start time, end time, and subject.'], 400);
        }
        $state['classRoutine'][$day][] = [
            'id' => makeId('p'),
            'start' => $start,
            'end' => $end,
            'subject' => $subject,
            'faculty' => s($input['faculty'] ?? ''),
            'room' => s($input['room'] ?? ''),
        ];
        saveData($state);
        respondWithState($state);
        break;
    }

    case 'editPeriod': {
        requireAdmin();
        $day = s($input['day'] ?? '');
        $id = s($input['id'] ?? '');
        $start = s($input['start'] ?? '');
        $end = s($input['end'] ?? '');
        $subject = s($input['subject'] ?? '');

        if (isset($state['classRoutine'][$day])) {
            foreach ($state['classRoutine'][$day] as &$p) {
                if ($p['id'] === $id) {
                    $p['start'] = $start !== '' ? $start : $p['start'];
                    $p['end'] = $end !== '' ? $end : $p['end'];
                    $p['subject'] = $subject !== '' ? $subject : $p['subject'];
                    $p['faculty'] = s($input['faculty'] ?? $p['faculty']);
                    $p['room'] = s($input['room'] ?? $p['room']);
                    break;
                }
            }
        }
        saveData($state);
        respondWithState($state);
        break;
    }

    case 'deletePeriod': {
        requireAdmin();
        $day = s($input['day'] ?? '');
        $id = s($input['id'] ?? '');
        if (isset($state['classRoutine'][$day])) {
            $state['classRoutine'][$day] = array_values(array_filter(
                $state['classRoutine'][$day],
                fn($p) => $p['id'] !== $id
            ));
        }
        saveData($state);
        respondWithState($state);
        break;
    }

    /* ---------------- EXAM ROUTINE ---------------- */

    case 'addExam': {
        requireAdmin();
        $subject = s($input['subject'] ?? '');
        $date = s($input['date'] ?? '');
        if ($subject === '' || $date === '') {
            jsonResponse(['ok' => false, 'error' => 'Please fill at least the Subject and Date fields.'], 400);
        }
        $state['examRoutine'][] = [
            'id' => makeId('e'),
            'subject' => $subject,
            'examType' => s($input['examType'] ?? ''),
            'topic' => s($input['topic'] ?? ''),
            'date' => $date,
            'time' => s($input['time'] ?? ''),
            'room' => s($input['room'] ?? ''),
            'isPinned' => true,
        ];
        saveData($state);
        respondWithState($state);
        break;
    }

    case 'editExam': {
        requireAdmin();
        $id = s($input['id'] ?? '');
        $subject = s($input['subject'] ?? '');
        $date = s($input['date'] ?? '');
        if ($id === '' || $subject === '' || $date === '') {
            jsonResponse(['ok' => false, 'error' => 'Please fill at least the Subject and Date fields.'], 400);
        }
        foreach ($state['examRoutine'] as &$e) {
            if ($e['id'] === $id) {
                $e['subject'] = $subject;
                $e['examType'] = s($input['examType'] ?? $e['examType']);
                $e['topic'] = s($input['topic'] ?? '');
                $e['date'] = $date;
                $e['time'] = s($input['time'] ?? '');
                $e['room'] = s($input['room'] ?? '');
                break;
            }
        }
        unset($e);
        saveData($state);
        respondWithState($state);
        break;
    }

    case 'togglePinExam': {
        requireAdmin();
        $id = s($input['id'] ?? '');
        foreach ($state['examRoutine'] as &$e) {
            if ($e['id'] === $id) { $e['isPinned'] = ($e['isPinned'] ?? true) === false ? true : false; }
        }
        unset($e);
        saveData($state);
        respondWithState($state);
        break;
    }

    case 'deleteExam': {
        requireAdmin();
        $id = s($input['id'] ?? '');
        $state['examRoutine'] = array_values(array_filter($state['examRoutine'], fn($e) => $e['id'] !== $id));
        saveData($state);
        respondWithState($state);
        break;
    }

    /* ---------------- FACULTY ---------------- */

    case 'addFaculty': {
        requireAdmin();
        $name = s($input['name'] ?? '');
        $email = strtolower(trim(s($input['email'] ?? '')));
        $semester = strtolower(trim(s($input['semester'] ?? '')));
        $courseCode = strtolower(trim(s($input['courseCode'] ?? '')));

        if ($name === '') {
            jsonResponse(['ok' => false, 'error' => 'Please enter a name.'], 400);
        }

        if ($email !== '') {
            foreach ($state['faculty'] as $f) {
                $exEmail = strtolower(trim($f['email'] ?? ''));
                $exSem = strtolower(trim($f['semester'] ?? ''));
                $exCourse = strtolower(trim($f['courseCode'] ?? ''));
                if ($exEmail === $email && $exSem === $semester && $exCourse === $courseCode) {
                    jsonResponse(['ok' => false, 'error' => 'A faculty with this email and course code already exists for this semester.'], 400);
                }
            }
        }

        $facultyId = makeId('f');
        $uploadedPath = handleFacultyUpload($facultyId);

        $state['faculty'][] = [
            'id' => $facultyId,
            'name' => $name,
            'designation' => s($input['designation'] ?? ''),
            'subject' => s($input['subject'] ?? ''),
            'courseCode' => s($input['courseCode'] ?? ''),
            'email' => s($input['email'] ?? ''),
            'phone' => s($input['phone'] ?? ''),
            'semester' => s($input['semester'] ?? ''),
            'imageUrl' => $uploadedPath ?: 'uploads/faculty/default.jpg',
            'classroomCode' => s($input['classroomCode'] ?? ''),
            'isAdvisor' => !empty($input['isAdvisor']) && ($input['isAdvisor'] === 'true' || $input['isAdvisor'] === true || $input['isAdvisor'] === '1'),
        ];
        saveData($state);
        respondWithState($state);
        break;
    }

    case 'editFaculty': {
        requireAdmin();
        $id = s($input['id'] ?? '');
        foreach ($state['faculty'] as &$f) {
            if ($f['id'] === $id) {
                $f['name'] = s($input['name'] ?? $f['name']);
                $f['designation'] = s($input['designation'] ?? $f['designation']);
                $f['subject'] = s($input['subject'] ?? $f['subject']);
                $f['courseCode'] = s($input['courseCode'] ?? $f['courseCode']);
                $f['email'] = s($input['email'] ?? $f['email']);
                $f['phone'] = s($input['phone'] ?? $f['phone']);
                $f['semester'] = s($input['semester'] ?? $f['semester']);
                $f['classroomCode'] = s($input['classroomCode'] ?? $f['classroomCode']);
                $f['isAdvisor'] = !empty($input['isAdvisor']) && ($input['isAdvisor'] === 'true' || $input['isAdvisor'] === true || $input['isAdvisor'] === '1');

                $uploadedPath = handleFacultyUpload($id);
                if ($uploadedPath !== '') {
                    $f['imageUrl'] = $uploadedPath;
                }
                break;
            }
        }
        saveData($state);
        respondWithState($state);
        break;
    }

    case 'deleteFaculty': {
        requireAdmin();
        $id = s($input['id'] ?? '');
        $state['faculty'] = array_values(array_filter($state['faculty'], fn($f) => $f['id'] !== $id));
        saveData($state);
        respondWithState($state);
        break;
    }

    /* ---------------- ROSTER / ATTENDANCE ---------------- */

    case 'addRosterStudent': {
        requireAdmin();
        $sid = s($input['sid'] ?? '');
        $name = s($input['name'] ?? '');
        if ($sid === '' || $name === '') {
            jsonResponse(['ok' => false, 'error' => 'Enter both Student ID and Name.'], 400);
        }
        foreach ($state['roster'] as $s) {
            if ($s['sid'] === $sid) {
                jsonResponse(['ok' => false, 'error' => 'A student with this ID already exists!'], 400);
            }
        }
        $state['roster'][] = ['sid' => $sid, 'name' => $name];
        saveData($state);
        respondWithState($state);
        break;
    }

    case 'removeRosterStudent': {
        requireAdmin();
        $sid = s($input['sid'] ?? '');
        $state['roster'] = array_values(array_filter($state['roster'], fn($s) => $s['sid'] !== $sid));
        saveData($state);
        respondWithState($state);
        break;
    }

    case 'saveAttendance': {
        requireAdmin();
        $date = s($input['date'] ?? '');
        $subject = s($input['subject'] ?? '');
        $sids = is_array($input['sids'] ?? null) ? $input['sids'] : [];
        $sids = array_map('strval', $sids);

        $students = array_values(array_filter($state['roster'], fn($s) => in_array($s['sid'], $sids, true)));
        usort($students, fn($a, $b) => strnatcmp($a['sid'], $b['sid']));

        if ($subject === '' || count($students) === 0) {
            jsonResponse(['ok' => false, 'error' => 'Select a subject and tap at least one student card to mark them Present.'], 400);
        }

        $state['attendance'][] = [
            'id' => makeId('a'),
            'date' => $date,
            'subject' => $subject,
            'students' => $students,
            'totalPresent' => count($students),
        ];
        saveData($state);
        respondWithState($state);
        break;
    }

    case 'deleteAttendance': {
        requireAdmin();
        $id = s($input['id'] ?? '');
        $state['attendance'] = array_values(array_filter($state['attendance'], fn($a) => $a['id'] !== $id));
        saveData($state);
        respondWithState($state);
        break;
    }

    /* ---------------- TASKS ---------------- */

    case 'addTask': {
        requireAdmin();
        $title = s($input['title'] ?? '');
        $deadline = s($input['deadline'] ?? '');
        if ($title === '' || $deadline === '') {
            jsonResponse(['ok' => false, 'error' => 'Please fill task title and deadline.'], 400);
        }
        $state['tasks'][] = [
            'id' => makeId('t'),
            'title' => $title,
            'subject' => s($input['subject'] ?? ''),
            'deadline' => $deadline,
            'note' => s($input['note'] ?? ''),
            'status' => 'pending',
            'assignedDate' => gmdate('Y-m-d'),
            'isPinned' => true,
        ];
        saveData($state);
        respondWithState($state);
        break;
    }

    case 'editTask': {
        requireAdmin();
        $id = s($input['id'] ?? '');
        $title = s($input['title'] ?? '');
        $deadline = s($input['deadline'] ?? '');
        if ($id === '' || $title === '' || $deadline === '') {
            jsonResponse(['ok' => false, 'error' => 'Please fill task title and deadline.'], 400);
        }
        foreach ($state['tasks'] as &$t) {
            if ($t['id'] === $id) {
                $t['title'] = $title;
                $t['subject'] = s($input['subject'] ?? '');
                $t['deadline'] = $deadline;
                $t['note'] = s($input['note'] ?? '');
                break;
            }
        }
        unset($t);
        saveData($state);
        respondWithState($state);
        break;
    }

    case 'toggleTask': {
        requireAdmin();
        $id = s($input['id'] ?? '');
        foreach ($state['tasks'] as &$t) {
            if ($t['id'] === $id) { $t['status'] = $t['status'] === 'done' ? 'pending' : 'done'; }
        }
        unset($t);
        saveData($state);
        respondWithState($state);
        break;
    }

    case 'togglePinTask': {
        requireAdmin();
        $id = s($input['id'] ?? '');
        foreach ($state['tasks'] as &$t) {
            if ($t['id'] === $id) { $t['isPinned'] = ($t['isPinned'] ?? true) === false ? true : false; }
        }
        unset($t);
        saveData($state);
        respondWithState($state);
        break;
    }

    case 'deleteTask': {
        requireAdmin();
        $id = s($input['id'] ?? '');
        $state['tasks'] = array_values(array_filter($state['tasks'], fn($t) => $t['id'] !== $id));
        saveData($state);
        respondWithState($state);
        break;
    }

    default:
        jsonResponse(['ok' => false, 'error' => 'Unknown action: ' . $action], 400);
}