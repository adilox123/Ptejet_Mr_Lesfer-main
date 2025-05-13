<?php
// Connexion à la base de données
$host = 'localhost';
$dbname = 'gestion_scolarite';
$user = 'root'; // À remplacer par votre nom d'utilisateur
$password = '';     // À remplacer par votre mot de passe

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Fonction pour ajouter une évaluation
function ajouterEvaluation($id_etudiant, $id_matiere, $note, $date_evaluation) {
    global $pdo;
    
    $sql = "INSERT INTO evaluations (id_étudiant, id_matiere, note, date_evaluation) 
            VALUES (:id_etudiant, :id_matiere, :note, :date_evaluation)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_etudiant', $id_etudiant);
    $stmt->bindParam(':id_matiere', $id_matiere);
    $stmt->bindParam(':note', $note);
    $stmt->bindParam(':date_evaluation', $date_evaluation);
    
    return $stmt->execute();
}               

// Fonction pour récupérer toutes les évaluations
function getEvaluations() {
    global $pdo;
    
    $sql = "SELECT * FROM evaluations";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour récupérer une évaluation par son ID
function getEvaluationById($id_evaluation) {
    global $pdo;
    
    $sql = "SELECT * FROM evaluations WHERE id_evaluation = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id_evaluation);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fonction pour mettre à jour une évaluation
function updateEvaluation($id_evaluation, $id_etudiant, $id_matiere, $note, $date_evaluation) {
    global $pdo;
    
    $sql = "UPDATE evaluations 
            SET id_étudiant = :id_etudiant, 
                id_matiere = :id_matiere, 
                note = :note, 
                date_evaluation = :date_evaluation 
            WHERE id_evaluation = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id_evaluation);
    $stmt->bindParam(':id_etudiant', $id_etudiant);
    $stmt->bindParam(':id_matiere', $id_matiere);
    $stmt->bindParam(':note', $note);
    $stmt->bindParam(':date_evaluation', $date_evaluation);
    
    return $stmt->execute();
}

// Fonction pour supprimer une évaluation
function deleteEvaluation($id_evaluation) {
    global $pdo;
    
    $sql = "DELETE FROM evaluations WHERE id_evaluation = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id_evaluation);
    
    return $stmt->execute();
}

// Exemples d'utilisation
try {
    // Ajouter une évaluation
    ajouterEvaluation(4, 18, 16.0, '2025-05-10');
    
    // Récupérer toutes les évaluations
    $evaluations = getEvaluations();
    echo "<h2>Liste des évaluations</h2>";
    echo "<pre>" . print_r($evaluations, true) . "</pre>";
    
    // Mettre à jour une évaluation
    updateEvaluation(1, 3, 15, 16.0, '2025-04-01');
    
    // Récupérer une évaluation spécifique
    $eval = getEvaluationById(2);
    echo "<h2>Évaluation #2</h2>";
    echo "<pre>" . print_r($eval, true) . "</pre>";
    
    // Supprimer une évaluation
    // deleteEvaluation(3);
    
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des évaluations</title>
</head>
<body>
    <h1>Gestion des évaluations</h1>
    
    <h2>Ajouter une évaluation</h2>
    <form method="post" action="">
        <label>ID Étudiant: <input type="number" name="id_etudiant" required></label><br>
        <label>ID Matière: <input type="number" name="id_matiere" required></label><br>
        <label>Note: <input type="number" step="0.1" name="note" required></label><br>
        <label>Date: <input type="date" name="date_evaluation" required></label><br>
        <button type="submit" name="ajouter">Ajouter</button>
    </form>
    
    <?php
    if (isset($_POST['ajouter'])) {
        $id_etudiant = $_POST['id_etudiant'];
        $id_matiere = $_POST['id_matiere'];
        $note = $_POST['note'];
        $date_evaluation = $_POST['date_evaluation'];
        
        if (ajouterEvaluation($id_etudiant, $id_matiere, $note, $date_evaluation)) {
            echo "<p>Évaluation ajoutée avec succès!</p>";
            header("Refresh:0"); // Rafraîchir la page
        } else {
            echo "<p>Erreur lors de l'ajout.</p>";
        }
    }
    ?>
    
    <h2>Liste des évaluations</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>ID Étudiant</th>
            <th>ID Matière</th>
            <th>Note</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
        <?php foreach (getEvaluations() as $eval): ?>
        <tr>
            <td><?= $eval['id_evaluation'] ?></td>
            <td><?= $eval['id_etudiant'] ?></td>
            <td><?= $eval['id_matiere'] ?></td>
            <td><?= $eval['note'] ?></td>
            <td><?= $eval['date_evaluation'] ?></td>
            <td>
                <a href="?edit=<?= $eval['id_evaluation'] ?>">Éditer</a> |
                <a href="?delete=<?= $eval['id_evaluation'] ?>" onclick="return confirm('Supprimer cette évaluation?')">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <?php
    // Gestion de la suppression
    if (isset($_GET['delete'])) {
        $id = $_GET['delete'];
        if (deleteEvaluation($id)) {
            echo "<p>Évaluation supprimée avec succès!</p>";
            header("Location: ".strtok($_SERVER['REQUEST_URI'], '?'));
        } else {
            echo "<p>Erreur lors de la suppression.</p>";
        }
    }
    
    // Gestion de l'édition
    if (isset($_GET['edit'])) {
        $eval = getEvaluationById($_GET['edit']);
        if ($eval):
    ?>
    <h2>Modifier l'évaluation #<?= $eval['id_evaluation'] ?></h2>
    <form method="post" action="">
        <input type="hidden" name="id_evaluation" value="<?= $eval['id_evaluation'] ?>">
        <label>ID Étudiant: <input type="number" name="id_etudiant" value="<?= $eval['id_etudiant'] ?>" required></label><br>
        <label>ID Matière: <input type="number" name="id_matiere" value="<?= $eval['id_matiere'] ?>" required></label><br>
        <label>Note: <input type="number" step="0.1" name="note" value="<?= $eval['note'] ?>" required></label><br>
        <label>Date: <input type="date" name="date_evaluation" value="<?= $eval['date_evaluation'] ?>" required></label><br>
        <button type="submit" name="modifier">Modifier</button>
    </form>
    <?php
        endif;
    }
    
    if (isset($_POST['modifier'])) {
        $id_evaluation = $_POST['id_evaluation'];
        $id_etudiant = $_POST['id_etudiant'];
        $id_matiere = $_POST['id_matiere'];
        $note = $_POST['note'];
        $date_evaluation = $_POST['date_evaluation'];
        
        if (updateEvaluation($id_evaluation, $id_etudiant, $id_matiere, $note, $date_evaluation)) {
            echo "<p>Évaluation modifiée avec succès!</p>";
            header("Location: ".strtok($_SERVER['REQUEST_URI'], '?'));
        } else {
            echo "<p>Erreur lors de la modification.</p>";
        }
    }
    ?>
</body>
</html>