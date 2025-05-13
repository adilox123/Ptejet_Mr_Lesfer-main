<?php
session_start();
$conn = new mysqli("localhost", "root", "", "gestion_scolarite");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Charger les filières
$filieres = [];
$res = $conn->query("SELECT id_filiere, nom_filiere FROM filieres");
while ($row = $res->fetch_assoc()) {
    $filieres[] = $row;
}

$message = "";
$role_choisi = $_POST['role'] ?? '';

// Traitement final si tous les champs sont remplis
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['nom'], $_POST['prenom'], $_POST['username'], $_POST['email'], $_POST['password'], $_POST['role']) &&
    ($_POST['role'] !== 'etudiant' || (isset($_POST['date_naissance'], $_POST['id_filiere']) && $_POST['id_filiere'] !== ''))
) {
    $username = trim($_POST['username']);
    $gmail = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $id_filiere = isset($_POST['id_filiere']) ? (int)$_POST['id_filiere'] : null;
    $date_naissance = $_POST['date_naissance'] ?? null;

    $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE username = ? OR gmail = ?");
    $stmt->bind_param("ss", $username, $gmail);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $message = "❌ Nom d'utilisateur ou email déjà utilisé.";
    } else {
        $stmt = $conn->prepare("INSERT INTO utilisateurs (username, gmail, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $gmail, $password, $role);
        if ($stmt->execute()) {
            if ($role === "etudiant") {
                $stmt = $conn->prepare("INSERT INTO etudiants (nom, prenom, date_naissance, id_filiere) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sssi", $nom, $prenom, $date_naissance, $id_filiere);
                $stmt->execute();
            } elseif ($role === "enseignant") {
                $stmt = $conn->prepare("INSERT INTO enseignants (nom, prenom, email) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $nom, $prenom, $gmail);
                $stmt->execute();
            }
            $message = "✅ Utilisateur et $role ajoutés avec succès.";
            $_POST = []; // Réinitialise le formulaire
        } else {
            $message = "❌ Erreur lors de l'ajout : " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un utilisateur</title>
    <style>
        body { font-family: Arial; background: #f9f9f9; padding: 30px; }
        form { background: white; padding: 20px; max-width: 500px; margin: auto; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        input, select { width: 100%; padding: 10px; margin-bottom: 15px; }
        input[type="submit"] { background-color: #3498db; color: white; border: none; }
        .message { text-align: center; font-weight: bold; color: #2c3e50; }
        h2 { text-align: center; }
    </style>
</head>
<body>

<h2>Ajouter un utilisateur</h2>

<form method="post" action="">
    <label>Nom :</label>
    <input type="text" name="nom" required value="<?= $_POST['nom'] ?? '' ?>">

    <label>Prénom :</label>
    <input type="text" name="prenom" required value="<?= $_POST['prenom'] ?? '' ?>">

    <label>Nom d'utilisateur :</label>
    <input type="text" name="username" required value="<?= $_POST['username'] ?? '' ?>">

    <label>Email (Gmail) :</label>
    <input type="email" name="email" pattern=".+@gmail\.com" required value="<?= $_POST['email'] ?? '' ?>">

    <label>Mot de passe :</label>
    <input type="password" name="password" required>

    <label>Rôle :</label>
    <select name="role" onchange="this.form.submit()">
        <option value="">-- Choisir un rôle --</option>
        <option value="admin" <?= ($role_choisi == 'admin') ? 'selected' : '' ?>>Administrateur</option>
        <option value="enseignant" <?= ($role_choisi == 'enseignant') ? 'selected' : '' ?>>Enseignant</option>
        <option value="etudiant" <?= ($role_choisi == 'etudiant') ? 'selected' : '' ?>>Étudiant</option>
    </select>

    <?php if ($role_choisi === 'etudiant'): ?>
        <label>Date de naissance :</label>
        <input type="date" name="date_naissance" required value="<?= $_POST['date_naissance'] ?? '' ?>">

        <label>Filière :</label>
        <select name="id_filiere" required>
            <option value="">-- Sélectionner une filière --</option>
            <?php foreach ($filieres as $f): ?>
                <option value="<?= $f['id_filiere'] ?>" <?= (isset($_POST['id_filiere']) && $_POST['id_filiere'] == $f['id_filiere']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['nom_filiere']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <input type="submit" value="Ajouter">
</form>

<?php if ($message): ?>
    <div class="message"><?= $message ?></div>
<?php endif; ?>

<p style="text-align:center;"><a href="exemple.php">⬅ Retour au tableau de bord</a></p>

</body>
</html>
