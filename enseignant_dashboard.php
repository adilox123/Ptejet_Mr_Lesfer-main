<?php
session_start();
require_once("./config/db.php");

// Vérifier si l'enseignant est connecté
if (!isset($_SESSION['id_enseignant'])) {
    header("Location: login.php");
    exit();
}

$enseignant_id = $_SESSION['id_enseignant'];

// Récupérer les infos de l'enseignant
$stmt = $pdo->prepare("SELECT * FROM enseignants WHERE id_enseignant = ?");
$stmt->execute([$enseignant_id]);
$enseignant = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier que l'enseignant existe
if (!$enseignant) {
    die("Enseignant introuvable.");
}

// Récupérer les matières de l'enseignant
$stmt_matieres = $pdo->prepare("
    SELECT m.*, f.nom_filiere 
    FROM matieres m
    JOIN filieres f ON m.id_filiere = f.id_filiere
    WHERE m.id_enseignant = ?
");
$stmt_matieres->execute([$enseignant_id]);
$matieres = $stmt_matieres->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les évaluations
$stmt_evaluations = $pdo->prepare("
    SELECT e.*, et.nom, et.prenom, m.nom_matiere
    FROM evaluations e
    JOIN etudiants et ON e.id_etudiant = et.id_etudiant
    JOIN matieres m ON e.id_matiere = m.id_matiere
    WHERE m.id_enseignant = ?
    ORDER BY e.date_evaluation DESC
");
$stmt_evaluations->execute([$enseignant_id]);
$evaluations = $stmt_evaluations->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Enseignant</title>
    <style>
        /* Styles similaires à ceux que tu avais envoyés */
        body {
            font-family: Arial;
            margin: 0;
            background-color: #f0f0f0;
        }
        .container { display: flex; }
        .sidebar {
            width: 220px;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            margin: 10px 0;
        }
        .main {
            flex: 1;
            padding: 20px;
        }
        h2 { margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #ccc; }
        th, td { padding: 10px; text-align: left; }
        .btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background-color: #007bff; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h3>Menu Enseignant</h3>
            <a href="#mes-matieres">Mes Matières</a>
            <a href="#saisie-notes">Saisie des Notes</a>
            <a href="#modifier-notes">Modifier des Notes</a>
            <a href="logout.php" style="color:#e74c3c;">Déconnexion</a>
        </div>

        <!-- Main Content -->
        <div class="main">
            <h2>Bienvenue, <?php echo htmlspecialchars($enseignant['prenom'] . ' ' . $enseignant['nom']); ?></h2>

            <!-- Mes Matières -->
            <section id="mes-matieres">
                <h3>Mes Matières</h3>
                <?php if ($matieres): ?>
                    <table>
                        <tr>
                            <th>Matière</th>
                            <th>Filière</th>
                        </tr>
                        <?php foreach ($matieres as $m): ?>
                            <tr>
                                <td><?= htmlspecialchars($m['nom_matiere']) ?></td>
                                <td><?= htmlspecialchars($m['nom_filiere']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <p>Aucune matière assignée.</p>
                <?php endif; ?>
            </section>

            <!-- Saisie des Notes -->
            <section id="saisie-notes">
                <h3>Saisie des Notes</h3>
                <form method="POST" action="enregistrer_note.php">
                    <label>Matière :</label>
                    <select name="id_matiere" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach ($matieres as $m): ?>
                            <option value="<?= $m['id_matiere'] ?>">
                                <?= htmlspecialchars($m['nom_matiere']) . " - " . htmlspecialchars($m['nom_filiere']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select><br><br>

                    <label>Étudiant :</label>
                    <input type="number" name="id_etudiant" placeholder="ID étudiant" required><br><br>

                    <label>Note (/20) :</label>
                    <input type="number" name="note" min="0" max="20" step="0.1" required><br><br>

                    <label>Date :</label>
                    <input type="date" name="date_evaluation" required><br><br>

                    <label>Type :</label>
                    <select name="type_evaluation" required>
                        <option value="cc">CC</option>
                        <option value="examen">Examen</option>
                        <option value="projet">Projet</option>
                    </select><br><br>

                    <button class="btn btn-primary">Enregistrer</button>
                </form>
            </section>

            <!-- Liste des Evaluations -->
            <section id="modifier-notes">
                <h3>Évaluations</h3>
                <?php if ($evaluations): ?>
                    <table>
                        <tr>
                            <th>Étudiant</th>
                            <th>Matière</th>
                            <th>Note</th>
                            <th>Type</th>
                            <th>Date</th>
                        </tr>
                        <?php foreach ($evaluations as $e): ?>
                            <tr>
                                <td><?= htmlspecialchars($e['prenom'] . ' ' . $e['nom']) ?></td>
                                <td><?= htmlspecialchars($e['nom_matiere']) ?></td>
                                <td><?= htmlspecialchars($e['note']) ?></td>
                                <td><?= htmlspecialchars($e['type_evaluation']) ?></td>
                                <td><?= htmlspecialchars($e['date_evaluation']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <p>Aucune évaluation enregistrée.</p>
                <?php endif; ?>
            </section>
        </div>
    </div>
</body>
</html>
