<?php
session_start();
require_once '../config.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get student details
$student_id = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    $_SESSION['error'] = 'Student record not found. Please contact the administrator.';
    header('Location: ../index.php');
    exit();
}

// Classify elections with the same date logic used on cast_vote.php.
$active_elections = [];
$upcoming_elections = [];
$stmt = $conn->prepare("SELECT *,
    CASE
        WHEN NOW() BETWEEN start_date AND end_date THEN 'Currently Active'
        WHEN NOW() < start_date THEN 'Upcoming'
        WHEN NOW() > end_date THEN 'Ended'
    END AS time_status
    FROM elections
    WHERE status = 'active'
    ORDER BY start_date ASC");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    if ($row['time_status'] === 'Currently Active') {
        $active_elections[] = $row;
    } elseif ($row['time_status'] === 'Upcoming') {
        $upcoming_elections[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'includes/student_styles.php'; ?>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <h1>Welcome, <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>!</h1>
            <p>Here's what's happening in your department</p>
        </div>

        <div class="dashboard-cards">
            <div class="card">
                <h2 class="card-title">
                    <i class="fas fa-vote-yea"></i>
                    Active Elections
                </h2>
                <?php if (empty($active_elections)): ?>
                    <p>No active elections at the moment.</p>
                <?php else: ?>
                    <?php foreach ($active_elections as $election): ?>
                        <div class="election-card">
                            <h3 class="election-title"><?php echo htmlspecialchars($election['title']); ?></h3>
                            <p class="election-description"><?php echo htmlspecialchars($election['description']); ?></p>
                            <p class="election-date">
                                <i class="far fa-calendar-alt"></i>
                                Start: <?php echo date('M d, Y', strtotime($election['start_date'])); ?><br>
                                End: <?php echo date('M d, Y', strtotime($election['end_date'])); ?>
                            </p>
                            <a href="cast_vote.php?election=<?php echo urlencode($election['election_id']); ?>" class="btn btn-primary">
                                <i class="fas fa-vote-yea"></i> Cast Vote
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2 class="card-title">
                    <i class="fas fa-calendar"></i>
                    Upcoming Elections
                </h2>
                <?php if (empty($upcoming_elections)): ?>
                    <p>No upcoming elections scheduled.</p>
                <?php else: ?>
                    <?php foreach ($upcoming_elections as $election): ?>
                        <div class="election-card">
                            <h3 class="election-title"><?php echo htmlspecialchars($election['title']); ?></h3>
                            <p class="election-description"><?php echo htmlspecialchars($election['description']); ?></p>
                            <p class="election-date">
                                <i class="far fa-calendar-alt"></i>
                                Starts: <?php echo date('M d, Y g:i A', strtotime($election['start_date'])); ?><br>
                                Ends: <?php echo date('M d, Y g:i A', strtotime($election['end_date'])); ?>
                            </p>
                            <div class="election-status">
                                <span class="badge badge-upcoming">Upcoming</span>
                                <p class="voting-info">Voting will be available when the election starts.</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2 class="card-title">
                    <i class="fas fa-user"></i>
                    Your Information
                </h2>
                <p><strong>Registration No:</strong> <?php echo htmlspecialchars($student['registration_no']); ?></p>
                <p><strong>Class Roll:</strong> <?php echo htmlspecialchars($student['class_roll']); ?></p>
                <p><strong>Department:</strong> <?php echo htmlspecialchars($student['department']); ?></p>
                <p><strong>Session:</strong> <?php echo htmlspecialchars($student['session']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                <a href="profile.php" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
