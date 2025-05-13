<?php
// Sécurisation de la page pour s'assurer que l'utilisateur est un admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin</title>
    <link rel="stylesheet" href="style.css"> <!-- Si tu as un fichier CSS -->
</head>
<body>

    <header>
        <h1>Bienvenue sur le tableau de bord de l'admin</h1>
        <p>Bonjour, <?php echo htmlspecialchars($_SESSION['username']); ?> !</p>
        <a href="login.php">Déconnexion</a>
    </header>

    <nav>
        <ul>
            <li><a href="admin_dashboard.php">Accueil</a></li>
            <li><a href="users.php">Gérer les utilisateurs</a></li>
            <li><a href="menu.php">Menu de Gestion</a></li>
            <li><a href="stats.php">Statistiques</a></li>
        </ul>
    </nav>

    <main>
        <h2>Gestion des utilisateurs</h2>
        <p><a href="add_user.php">Ajouter un nouvel utilisateur</a></p>

        <!-- Exemple de tableau d'utilisateurs -->
        <table border="1">
            <thead>
                <tr>
                    <th>Nom d'utilisateur</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Connexion à la base de données
                try {
                    $conn = new PDO("mysql:host=localhost;dbname=gestion_scolarite", "root", "");
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (PDOException $e) {
                    die("Erreur de connexion : " . $e->getMessage());
                }

                // Requête pour récupérer tous les utilisateurs
                $stmt = $conn->prepare("SELECT id, username, role FROM utilisateurs");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Affichage des utilisateurs dans le tableau
                foreach ($users as $user) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['role']) . "</td>";
                    echo "<td><a href='edit_user.php?id=" . $user['id'] . "'>Modifier</a> | <a href='delete_user.php?id=" . $user['id'] . "'>Supprimer</a></td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </main>

</body>
</html>
