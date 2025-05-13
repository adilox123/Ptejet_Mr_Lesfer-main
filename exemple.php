<?php
// Sécurisation de la page pour s'assurer que l'utilisateur est un admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
try {
    $conn = new PDO("mysql:host=localhost;dbname=gestion_scolarite", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Gestion du filtre
$filtre_role = $_GET['role'] ?? 'tous';
$where_clause = ($filtre_role !== 'tous') ? "WHERE role = :role" : "";
$sql = "SELECT * FROM utilisateurs $where_clause";
$stmt = $conn->prepare($sql);

if ($filtre_role !== 'tous') {
    $stmt->bindParam(':role', $filtre_role);
}

$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
            margin: 0;
        }
        header {
            background: #343a40;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
        }
        nav ul {
            list-style-type: none;
            padding: 0;
            background: #007bff;
            overflow: hidden;
            margin: 0;
        }
        nav li {
            float: left;
        }
        nav li a {
            display: block;
            color: white;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }
        nav li a:hover {
            background-color: #0056b3;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .filter-buttons {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn {
            padding: 10px 15px;
            margin: 5px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }
        .btn-primary {
            background-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-success {
            background-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .btn-info {
            background-color: #17a2b8;
        }
        .btn-info:hover {
            background-color: #138496;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #343a40;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .action-cell {
            text-align: center;
        }
    </style>
</head>
<body>

<header>
    <div class="container">
        <h1>Bienvenue sur le tableau de bord de l'admin</h1>
        <p>Bonjour, <?php echo htmlspecialchars($_SESSION['username']); ?> !</p>
    </div>
</header>

<nav>
    <ul>
        <li><a href="admin_dashboard.php">Accueil</a></li>
        <li><a href="users.php">Gérer les utilisateurs</a></li>
        <li><a href="Gestion_pedagogique.php">Gestion Pédagogique</a></li>
        <li><a href="admin_stats.php">Statistiques</a></li>
        <li><a href="logout.php">Déconnexion</a></li>
    </ul>
</nav>

<div class="container">
    <h2>Gestion des utilisateurs</h2>

    <div class="filter-buttons">
        <a href="?role=tous" class="btn btn-primary">Tous</a>
        <a href="?role=admin" class="btn btn-primary">Admins</a>
        <a href="?role=enseignant" class="btn btn-primary">Enseignants</a>
        <a href="?role=etudiant" class="btn btn-primary">Étudiants</a>
        <a href="add_user.php" class="btn btn-info">➕ Ajouter un utilisateur</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nom d'utilisateur</th>
                <th>Rôle</th>
                <th>Email</th>
                <th class="action-cell">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
                <td><?= htmlspecialchars($user['gmail']) ?></td>
                <td class="action-cell">
                    <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-success">Modifier</a>
                    <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>