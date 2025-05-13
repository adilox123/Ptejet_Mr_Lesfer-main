<?php
$host = "localhost";
$db = "gestion_scolarite";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Statistiques générales
$nbrEtudiants = $pdo->query("SELECT COUNT(*) FROM etudiants")->fetchColumn();
$nbrEnseignants = $pdo->query("SELECT COUNT(*) FROM enseignants")->fetchColumn();
$nbrMatieres = $pdo->query("SELECT COUNT(*) FROM matieres")->fetchColumn();
$nbrFilieres = $pdo->query("SELECT COUNT(*) FROM filieres")->fetchColumn();
$moyenneGenerale = $pdo->query("SELECT ROUND(AVG(note), 2) FROM evaluations")->fetchColumn();

// Top 5 étudiants
$topEtudiants = $pdo->query("
    SELECT e.id_etudiant, e.nom, e.prenom, ROUND(AVG(ev.note), 2) AS moyenne
    FROM etudiants e
    JOIN evaluations ev ON e.id_etudiant = ev.id_etudiant
    GROUP BY e.id_etudiant
    ORDER BY moyenne DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Notes par matière
$notesParMatiere = $pdo->query("
    SELECT m.nom_matiere, COUNT(ev.id_evaluation) as total
    FROM matieres m
    LEFT JOIN evaluations ev ON m.id_matiere = ev.id_matiere
    GROUP BY m.nom_matiere
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques administrateur</title>
    <style>
        body { font-family: Arial; margin: 30px; background: #f2f2f2; }
        h1 { color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background-color: #3498db; color: white; }
        .box { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

    <h1>Tableau de bord - Statistiques</h1>

    <div class="box">
        <h2>Statistiques générales</h2>
        <p>Nombre d'étudiants : <strong><?= $nbrEtudiants ?></strong></p>
        <p>Nombre d'enseignants : <strong><?= $nbrEnseignants ?></strong></p>
        <p>Nombre de matières : <strong><?= $nbrMatieres ?></strong></p>
        <p>Nombre de filières : <strong><?= $nbrFilieres ?></strong></p>
        <p>Moyenne générale des notes : <strong><?= $moyenneGenerale ?></strong></p>
    </div>

    <div class="box">
        <h2>Top 5 des étudiants</h2>
        <table>
            <tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Moyenne</th></tr>
            <?php foreach ($topEtudiants as $etudiant): ?>
            <tr>
                <td><?= $etudiant['id_etudiant'] ?></td>
                <td><?= $etudiant['nom'] ?></td>
                <td><?= $etudiant['prenom'] ?></td>
                <td><?= $etudiant['moyenne'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="box">
        <h2>Nombre de notes par matière</h2>
        <table>
            <tr><th>Matière</th><th>Nombre de notes</th></tr>
            <?php foreach ($notesParMatiere as $matiere): ?>
            <tr>
                <td><?= $matiere['nom_matiere'] ?></td>
                <td><?= $matiere['total'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

</body>
</html>
