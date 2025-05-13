<?php
// Connexion à la base de données
$host = "localhost";
$user = "root";
$pass = "";
$db = "gestion_scolarite";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Traitement ajout matière
if (isset($_POST['add_matiere'])) {
    $nom = $_POST['nom_matiere'];
    $filiere = $_POST['id_filiere'];
    $enseignant = $_POST['id_enseignant'];
    $conn->query("INSERT INTO matieres (nom_matiere, id_filiere, id_enseignant) VALUES ('$nom', $filiere, $enseignant)");
}

// Traitement ajout filière
if (isset($_POST['add_filiere'])) {
    $nom = $_POST['nom_filiere'];
    $conn->query("INSERT INTO filieres (nom_filiere) VALUES ('$nom')");
}

// Traitement ajout évaluation
if (isset($_POST['add_evaluation'])) {
    $etudiant = $_POST['id_etudiant'];
    $matiere = $_POST['id_matiere'];
    $note = $_POST['note'];
    $date = $_POST['date_evaluation'];
    $conn->query("INSERT INTO evaluations (id_etudiant, id_matiere, note, date_evaluation) VALUES ($etudiant, $matiere, $note, '$date')");
}

// Récupération données pour les listes déroulantes
$filieres = $conn->query("SELECT * FROM filieres");
$enseignants = $conn->query("SELECT * FROM enseignants");
$etudiants = $conn->query("SELECT * FROM etudiants");
$matieres = $conn->query("SELECT * FROM matieres");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Pédagogique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
<div class="container">
    <h1 class="mb-4 text-center">Gestion Pédagogique</h1>

    <!-- Ajouter une matière -->
    <div class="card mb-4">
        <div class="card-header">Ajouter une matière</div>
        <div class="card-body">
            <form method="POST">
                <input type="text" name="nom_matiere" placeholder="Nom matière" class="form-control mb-2" required>
                <select name="id_filiere" class="form-control mb-2" required>
                    <option value="">Choisir une filière</option>
                    <?php while ($f = $filieres->fetch_assoc()) echo "<option value='{$f['id_filiere']}'>{$f['nom_filiere']}</option>"; ?>
                </select>
                <select name="id_enseignant" class="form-control mb-2" required>
                    <option value="">Choisir un enseignant</option>
                    <?php while ($e = $enseignants->fetch_assoc()) echo "<option value='{$e['id_enseignant']}'>{$e['nom']} {$e['prenom']}</option>"; ?>
                </select>
                <button type="submit" name="add_matiere" class="btn btn-primary">Ajouter</button>
            </form>
        </div>
    </div>

    <!-- Ajouter une filière -->
    <div class="card mb-4">
        <div class="card-header">Ajouter une filière</div>
        <div class="card-body">
            <form method="POST">
                <input type="text" name="nom_filiere" placeholder="Nom filière" class="form-control mb-2" required>
                <button type="submit" name="add_filiere" class="btn btn-success">Ajouter</button>
            </form>
        </div>
    </div>

    <!-- Ajouter une évaluation -->
    <div class="card mb-4">
        <div class="card-header">Ajouter une évaluation</div>
        <div class="card-body">
            <form method="POST">
                <select name="id_etudiant" class="form-control mb-2" required>
                    <option value="">Choisir un étudiant</option>
                    <?php while ($et = $etudiants->fetch_assoc()) echo "<option value='{$et['id_etudiant']}'>{$et['nom']} {$et['prenom']}</option>"; ?>
                </select>
                <select name="id_matiere" class="form-control mb-2" required>
                    <option value="">Choisir une matière</option>
                    <?php mysqli_data_seek($matieres, 0); while ($m = $matieres->fetch_assoc()) echo "<option value='{$m['id_matiere']}'>{$m['nom_matiere']}</option>"; ?>
                </select>
                <input type="number" name="note" min="0" max="20" step="0.01" class="form-control mb-2" placeholder="Note" required>
                <input type="date" name="date_evaluation" class="form-control mb-2" required>
                <button type="submit" name="add_evaluation" class="btn btn-warning">Ajouter</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
