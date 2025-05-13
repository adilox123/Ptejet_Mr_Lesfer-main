<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="login.css">
   
</head>
<body>
    
    <h2>Connexion</h2>
    <form action="login.php" method="post">
        <label>Gmail :</label>
        <input type="email" name="gmail" placeholder="Adresse Gmail" required>
        <br>
        <label>Mot de passe :</label>
        <input type="password" name="password" required>
        <br>
        <input type="submit" value="Se connecter">
    </form>
</body>
</html>
<?php
// Connexion à la base de données
try {
    $conn = new PDO("mysql:host=localhost;dbname=gestion_scolarite", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gmail = $_POST['gmail'];
    $password = $_POST['password'];

    // Recherche de l'utilisateur dans la base de données
    $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE gmail = ?");
    $stmt->execute([$gmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifier si l'utilisateur existe
    if ($user) {
        // Vérification du mot de passe haché
        if (password_verify($password, $user['password'])) {
            // Si le mot de passe est correct, démarrer une session
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];  // Récupérer le rôle

            // Rediriger selon le rôle de l'utilisateur
            if ($user['role'] == 'admin') {
                header("Location: exemple.php");
            } elseif ($user['role'] == 'enseignant') {
                header("Location: enseignant_dashboard.php");
            } else {
                header("Location: etudiant_dashboard.php");
            }
        } else {
            echo "Mot de passe incorrect.";
        }
    } else {
        echo "Utilisateur non trouvé.";
    }
}
?>
