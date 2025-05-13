<?php
session_start();

// 🔒 Vérification admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    $_SESSION['error'] = "Accès non autorisé";
    header("Location: login.php");
    exit();
}

// Connexion à la base de données MySQLi
$conn = new mysqli("localhost", "root", "", "gestion_scolarite");

// Vérification de la connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Gestion du filtre
$filtre_role = $_GET['role'] ?? 'tous';

if ($filtre_role === 'tous') {
    $sql = "SELECT * FROM utilisateurs";
    $result = $conn->query($sql);
} else {
    $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE role = ?");
    $stmt->bind_param("s", $filtre_role);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Récupération des résultats (méthode MySQLi)
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Fermeture de la connexion
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs</title>
    <style>
        body {
            font-family: Arial;
            padding: 20px;
            background: #f5f5f5;
        }
        h2 {
            text-align: center;
        }
        .buttons {
            text-align: center;
            margin-bottom: 20px;
        }
        .buttons a {
            padding: 10px 15px;
            margin: 5px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
        }
        .buttons a:hover {
            background-color: #0056b3;
        }
        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .action-btn {
            padding: 8px 12px;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 0 3px;
        }
        .edit-btn {
            background-color: #28a745;
        }
        .delete-btn {
            background-color: #dc3545;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>

<h2>Gestion des utilisateurs</h2>
<a href="login.php">Déconnexion</a>

<!-- Affichage des messages -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?= $_SESSION['success'] ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?= $_SESSION['error'] ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="buttons">
    <a href="users.php?role=tous">Tous</a>
    <a href="users.php?role=admin">Admins</a>
    <a href="users.php?role=enseignant">Enseignants</a>
    <a href="users.php?role=etudiant">Étudiants</a>
    <a href="add_user.php" style="background-color:#20c997;">➕ Ajouter un utilisateur</a>
</div>

<table>
    <thead>
        <tr>
            <th>Nom d'utilisateur</th>
            <th>Rôle</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if (count($users) > 0): ?>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            <td><?= htmlspecialchars($user['gmail']) ?></td>
            <td>
                <a class="action-btn edit-btn" href="edit_user.php?id=<?= $user['id'] ?>">Modifier</a>
                <a class="action-btn delete-btn" href="delete_user.php?id=<?= $user['id'] ?>" 
                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                   Supprimer
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="4" style="text-align: center;">Aucun utilisateur trouvé</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

</body>
</html>