<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get success message if any
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['success_message']);

// Get error message if any
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['error_message']);

// Fetch all elections
$elections_query = "SELECT e.*
                   FROM elections e
                   ORDER BY e.created_at DESC";
$elections_result = $conn->query($elections_query);
$elections = [];

if ($elections_result) {
    while ($row = $elections_result->fetch_assoc()) {
        $elections[] = $row;
    }
}

// Get selected election results if an election is chosen
$selected_election = null;
$election_results = [];

if (isset($_GET['election_id']) && !empty($_GET['election_id'])) {
    $election_id = $_GET['election_id'];

    // Get election details
    $election_query = "SELECT * FROM elections WHERE election_id = ?";
    $stmt = $conn->prepare($election_query);
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $selected_election = $result->fetch_assoc();

        // Get voting results
        $results_query = "SELECT c.*, COUNT(v.vote_id) as vote_count
                         FROM candidates c
                         LEFT JOIN votes v ON c.candidate_id = v.candidate_id
                         WHERE c.election_id = ?
                         GROUP BY c.candidate_id
                         ORDER BY vote_count DESC";
        $stmt = $conn->prepare($results_query);
        $stmt->bind_param("i", $election_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $election_results[] = $row;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Results - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'includes/admin_styles.php'; ?>

    <style>
        /* Scoped styles keep the shared admin layout intact. */
        .results-page .results-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            align-items: stretch;
        }

        .results-page .result-card {
            display: grid;
            grid-template-columns: 112px minmax(0, 1fr);
            align-items: center;
            gap: 18px;
            min-width: 0;
            padding: 16px;
            overflow: hidden;
            background: #fff;
            border: 1px solid #e8edf5;
            border-radius: 16px;
            box-shadow: 0 8px 22px rgba(25, 42, 70, 0.07);
            transition: transform 180ms ease, box-shadow 180ms ease;
        }

        .results-page .result-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(25, 42, 70, 0.11);
        }

        .results-page .candidate-info {
            display: flex;
            min-width: 0;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            margin: 0;
            text-align: center;
        }

        .results-page .candidate-photo,
        .results-page .no-photo {
            display: block;
            width: 104px;
            height: 116px;
            flex: 0 0 auto;
            object-fit: cover;
            object-position: center 25%;
            border: 4px solid #fff;
            border-radius: 14px;
            background: #eef2f8;
            box-shadow: 0 0 0 1px #dce4f0, 0 6px 16px rgba(31, 52, 85, 0.14);
        }

        .results-page .no-photo {
            display: grid;
            place-items: center;
            color: #7a8ba5;
            font-size: 32px;
        }

        .results-page .candidate-info h3 {
            width: 100%;
            margin: 0;
            overflow-wrap: anywhere;
            color: #202b3c;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.4;
        }

        .results-page .vote-info {
            display: flex;
            min-width: 0;
            flex-direction: column;
            gap: 18px;
        }

        .results-page .vote-count {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .results-page .vote-count .number {
            color: #1f2d42;
            font-size: clamp(24px, 3vw, 32px);
            font-weight: 700;
            line-height: 1.1;
        }

        .results-page .vote-count .label {
            color: #77849a;
            font-size: 12px;
        }

        .results-page .vote-percentage {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .results-page .progress {
            height: 9px;
            flex: 1;
            overflow: hidden;
            border-radius: 999px;
            background: #edf1f7;
        }

        .results-page .progress-bar {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #526fe5, #43b4d8);
        }

        .results-page .percentage {
            min-width: 46px;
            color: #44536a;
            font-size: 12px;
            font-weight: 600;
            text-align: right;
        }

        .results-page .total-votes {
            padding: 14px 16px;
            border-radius: 12px;
            background: #f5f7fb;
        }

        .results-page .total-votes h3 {
            margin: 0;
            color: #28364b;
            font-size: 15px;
            font-weight: 600;
        }

        .results-page .empty-results {
            margin: 0;
            color: #68778d;
        }

        .results-page .election-form .form-group {
            max-width: 520px;
            margin-bottom: 0;
        }

        .results-page .results-heading {
            overflow-wrap: anywhere;
        }

        @media (max-width: 900px) {
            .results-page .results-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 520px) {
            .results-page .result-card {
                grid-template-columns: 88px minmax(0, 1fr);
                gap: 14px;
                padding: 13px;
                border-radius: 14px;
            }

            .results-page .candidate-photo,
            .results-page .no-photo {
                width: 82px;
                height: 96px;
                border-radius: 12px;
            }

            .results-page .candidate-info h3 {
                font-size: 12px;
            }

            .results-page .vote-info {
                gap: 14px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .results-page .result-card {
                transition: none;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content results-page">
            <div class="top-bar">
                <h1 class="page-title">View Election Results</h1>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Election Selection -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Select Election</h2>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="election-form">
                        <div class="form-group">
                            <select name="election_id" class="form-control" onchange="this.form.submit()">
                                <option value="">Choose an election</option>
                                <?php foreach ($elections as $election): ?>
                                    <option value="<?php echo $election['election_id']; ?>"
                                        <?php echo (isset($_GET['election_id']) && $_GET['election_id'] == $election['election_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($election['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($selected_election): ?>
                <!-- Election Results -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h2 class="card-title results-heading">
                            Results: <?php echo htmlspecialchars($selected_election['title']); ?>
                        </h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($election_results)): ?>
                            <p class="empty-results">No candidates found for this election.</p>
                        <?php else: ?>
                            <div class="results-grid">
                                <?php foreach ($election_results as $result): ?>
                                    <div class="result-card">
                                        <div class="candidate-info">
                                            <?php if (!empty($result['photo'])): ?>
                                                <img src="../uploads/candidates/<?php echo htmlspecialchars($result['photo']); ?>"
                                                     alt="<?php echo htmlspecialchars($result['name']); ?>"
                                                     class="candidate-photo">
                                            <?php else: ?>
                                                <div class="no-photo" aria-label="No candidate photo">
                                                    <i class="fas fa-user" aria-hidden="true"></i>
                                                </div>
                                            <?php endif; ?>
                                            <h3><?php echo htmlspecialchars($result['name']); ?></h3>
                                        </div>

                                        <div class="vote-info">
                                            <div class="vote-count">
                                                <span class="number"><?php echo $result['vote_count']; ?></span>
                                                <span class="label">Votes</span>
                                            </div>
                                            <?php
                                            // Calculate percentage if there are any votes
                                            $total_votes = array_sum(array_column($election_results, 'vote_count'));
                                            $percentage = $total_votes > 0 ? ($result['vote_count'] / $total_votes) * 100 : 0;
                                            ?>
                                            <div class="vote-percentage">
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                                                </div>
                                                <span class="percentage"><?php echo number_format($percentage, 1); ?>%</span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="total-votes mt-4">
                                <h3>Total Votes: <?php echo $total_votes; ?></h3>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
