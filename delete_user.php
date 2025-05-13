<?php
session_start();
require_once(__DIR__.'/config/db.php');

// Vérification admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Accès non autorisé";
    header("Location: login.php");
    exit();
}

// Vérification ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID utilisateur invalide";
    header("Location: liste_utilisateurs.php");
    exit();
}

$id = intval($_GET['id']);
$username = "";

try {
    // Récupération du nom avant suppression
    $check = $conn->prepare("SELECT username FROM utilisateurs WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Utilisateur introuvable");
    }
    
    $user = $result->fetch_assoc();
    $username = $user['username'];

    // Traitement de la suppression si confirmation
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
        $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $success = "L'utilisateur '".htmlspecialchars($username)."' a été supprimé avec succès";
        } else {
            throw new Exception("Erreur lors de la suppression");
        }
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppression d'utilisateur</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        h1 {
            color: #dc3545;
            margin-top: 0;
        }
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .confirmation {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 5px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
            border: 1px solid #dc3545;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            border: 1px solid #6c757d;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($success)): ?>
            <h1>Suppression réussie</h1>
            <div class="message success">
                <?php echo $success; ?>
            </div>
            <a href="exemple.php" class="btn btn-secondary">Retour à la liste</a>
        
        <?php elseif (isset($error)): ?>
            <h1>Erreur</h1>
            <div class="message error">
                <?php echo $error; ?>
            </div>
            <a href="exemple.php" class="btn btn-secondary">Retour</a>
        
        <?php else: ?>
            <h1>Confirmer la suppression</h1>
            <div class="confirmation">
                <p>Êtes-vous sûr de vouloir supprimer l'utilisateur :</p>
                <h3><?php echo htmlspecialchars($username); ?></h3>
                <p>Cette action est irréversible.</p>
            </div>
            
            <form method="POST">
                <input type="hidden" name="confirm" value="1">
                <button type="submit" class="btn btn-danger">Confirmer la suppression</button>
                <a href="exemple.php" class="btn btn-secondary">Annuler</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>