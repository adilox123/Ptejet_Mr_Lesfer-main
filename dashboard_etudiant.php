<?php
session_start();

// 🔧 TEST LOCAL : définis un ID étudiant existant (à retirer en production)
if (!isset($_SESSION['id_etudiant'])) {
    $_SESSION['id_etudiant'] = 1; // ID dans la base (ex: Ahmed)
}

include("./config/db.php");

$id_etudiant = $_SESSION['id_etudiant'] ?? null;

if (!$id_etudiant) {
    echo "Accès refusé. Veuillez vous connecter.";
    exit;
}

// 🔍 Récupération des infos étudiant (requête sécurisée)
$etudiant_sql = "SELECT e.nom, e.prenom, f.nom_filiere 
                 FROM etudiants e 
                 JOIN filieres f ON e.id_filiere = f.id_filiere 
                 WHERE e.id_etudiant = ?";
$stmt = $conn->prepare($etudiant_sql);
$stmt->bind_param("i", $id_etudiant);
$stmt->execute();
$etudiant_result = $stmt->get_result();

if (!$etudiant_result || $etudiant_result->num_rows == 0) {
    echo "Erreur : étudiant non trouvé.";
    exit;
}

$etudiant = $etudiant_result->fetch_assoc();

// 📊 Récupération des notes (requête sécurisée)
$notes_sql = "SELECT m.nom_matiere, e.note, e.date_evaluation 
              FROM evaluations e 
              JOIN matieres m ON e.id_matiere = m.id_matiere 
              WHERE e.id_etudiant = ?";
$stmt = $conn->prepare($notes_sql);
$stmt->bind_param("i", $id_etudiant);
$stmt->execute();
$notes_result = $stmt->get_result();

$total = 0;
$count = 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord étudiant</title>
</head>
<body>
    <h2>Bienvenue, <?= htmlspecialchars($etudiant['prenom']) . " " . htmlspecialchars($etudiant['nom']) ?></h2>
    <p><strong>Filière :</strong> <?= htmlspecialchars($etudiant['nom_filiere']) ?></p>

    <h3>Relevé de notes</h3>
    <table border="1" cellpadding="10">
        <tr><th>Matière</th><th>Note</th><th>Date</th></tr>
        <?php while ($row = $notes_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['nom_matiere']) ?></td>
                <td><?= htmlspecialchars($row['note']) ?></td>
                <td><?= htmlspecialchars($row['date_evaluation']) ?></td>
            </tr>
            <?php
            $total += $row['note'];
            $count++;
            ?>
        <?php endwhile; ?>
    </table>

    <?php if ($count > 0): ?>
        <p><strong>Moyenne générale :</strong> <?= round($total / $count, 2) ?>/20</p>
    <?php else: ?>
        <p>Aucune note trouvée pour cet étudiant.</p>
    <?php endif; ?>

    <a href="deconnexion.php">Déconnexion</a>
</body>
</html>Y