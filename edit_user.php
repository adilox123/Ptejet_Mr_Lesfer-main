<?php
session_start();
require_once(__DIR__.'/config/db.php');

// Vérification admin
if (!isset($_SESSION['role'])) {
    $_SESSION['error'] = "Veuillez vous connecter";
    header("Location: login.php");
    exit;
}

// Vérification ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID utilisateur invalide";
    header("Location: liste_utilisateurs.php");
    exit;
}

$id = intval($_GET['id']);

// Récupération des données utilisateur
$stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['error'] = "Utilisateur introuvable";
    header("Location: liste_utilisateurs.php");
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    $email = trim($_POST['email']);

    // Validation des données
    if (empty($username) || empty($email)) {
        $error = "Tous les champs sont obligatoires";
    } else {
        $update_stmt = $conn->prepare("UPDATE utilisateurs SET username = ?, role = ?, gmail = ? WHERE id = ?");
        $update_stmt->bind_param("sssi", $username, $role, $email, $id);
        
        if ($update_stmt->execute()) {
            $success = "Utilisateur modifié avec succès";
        } else {
            $error = "Erreur lors de la modification : " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier utilisateur</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-top: 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button, .btn {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        button:hover, .btn:hover {
            background-color: #0056b3;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .success-container {
            text-align: center;
            padding: 20px;
        }
        .success-icon {
            font-size: 50px;
            color: #28a745;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($success)): ?>
            <div class="success-container">
                <div class="success-icon">✓</div>
                <h2>Modification réussie</h2>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <a href="exemple.php" class="btn">Retour à la liste des utilisateurs</a>
            </div>
        <?php else: ?>
            <h1>Modifier utilisateur</h1>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Nom d'utilisateur</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Rôle</label>
                    <select name="role" required>
                        <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="enseignant" <?php echo ($user['role'] == 'enseignant') ? 'selected' : ''; ?>>Enseignant</option>
                        <option value="etudiant" <?php echo ($user['role'] == 'etudiant') ? 'selected' : ''; ?>>Étudiant</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['gmail']); ?>" required>
                </div>

                <button type="submit">Enregistrer</button>
                <a href="liste_utilisateurs.php" style="margin-left: 10px;">Annuler</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>