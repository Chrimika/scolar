<!DOCTYPE html>
<html>
<head>
    <title>Seed - Création d'un Enfant</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <style>
        .nav-wrapper {
            background-color: #0096D6; /* Bleu ciel plus foncé */
        }
    </style>
</head>
<body>

<nav>
    <div class="nav-wrapper">
        <a href="index.php" class="brand-logo">Seed</a>
        <ul id="nav-mobile" class="right hide-on-med-and-down">
            <li><a href="groupes.php">Groupes</a></li>
            <li><a href="enfants.php">Enfants</a></li>
            <li><a href="seances.php">Séances</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <h2>Créer un Enfant</h2>
    <form method="POST" action="enfants_ajout.php" enctype="multipart/form-data">
        <div class="input-field">
            <input id="nom" name="nom" type="text" class="validate" required>
            <label for="nom">Nom</label>
        </div>
        <div class="input-field">
            <input id="prenom" name="prenom" type="text" class="validate" required>
            <label for="prenom">Prénom</label>
        </div>
        <div class="input-field">
            <input id="age" name="age" type="number" class="validate" required>
            <label for="age">Âge</label>
        </div>
        <div class="input-field">
            <input id="niveau" name="niveau" type="text" class="validate" required>
            <label for="niveau">Niveau</label>
        </div>
        <div class="input-field">
            <input id="tel_urgence" name="tel_urgence" type="text" class="validate" required>
            <label for="tel_urgence">Téléphone d'Urgence</label>
        </div>
        <div class="input-field">
            <select id="etat_financier" name="etat_financier" required>
                <option value="" disabled selected>Choisissez l'état financier</option>
                <option value="Complet">Complet</option>
                <option value="Incomplet">Incomplet</option>
            </select>
            <label for="etat_financier">État Financier</label>
        </div>
        <div class="input-field">
            <select id="id_groupes" name="id_groupes" required>
                <option value="" disabled selected>Choisissez un groupe</option>
                <?php
                try {
                    $pdo = new PDO('mysql:host=localhost;dbname=shoolar', 'root', '');
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $stmt = $pdo->query('SELECT ID_groupes, numero FROM groupe');
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<option value="' . $row['ID_groupes'] . '">' . $row['numero'] . '</option>';
                    }
                } catch (PDOException $e) {
                    echo '<script>M.toast({html: "Erreur : ' . $e->getMessage() . '", classes: "rounded"});</script>';
                }
                ?>
            </select>
            <label for="id_groupes">Groupe</label>
        </div>
        <div class="file-field input-field">
            <div class="btn">
                <span>Photo</span>
                <input type="file" name="photo">
            </div>
            <div class="file-path-wrapper">
                <input class="file-path validate" type="text" placeholder="Téléchargez une photo">
            </div>
        </div>
        <button class="btn waves-effect waves-light" type="submit" name="action">Créer
            <i class="material-icons right">send</i>
        </button>
    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var elems = document.querySelectorAll('select');
        M.FormSelect.init(elems);
    });
</script>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $age = $_POST['age'];
    $niveau = $_POST['niveau'];
    $tel_urgence = $_POST['tel_urgence'];
    $etat_financier = $_POST['etat_financier'];
    $id_groupes = $_POST['id_groupes'];

    // Gestion du téléchargement de photo
    $photo = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['photo']['tmp_name'];
        $fileName = $_FILES['photo']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $uploadFileDir = __DIR__ . '/uploads/';
        $dest_path = $uploadFileDir . $newFileName;

        // Crée le répertoire s'il n'existe pas
        if (!file_exists($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true);
        }

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $photo = $newFileName; // Enregistre seulement le nom du fichier
        } else {
            echo '<script>M.toast({html: "Erreur lors du téléchargement de la photo.", classes: "rounded"});</script>';
        }
    }

    try {
        $pdo = new PDO('mysql:host=localhost;dbname=shoolar', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Vérifier si l'enfant existe déjà
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM enfant WHERE Nom = :nom AND Prenom = :prenom');
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            echo '<script>M.toast({html: "Un enfant avec ce nom et prénom existe déjà.", classes: "rounded"});</script>';
        } else {
            // Ajouter l'enfant
            $stmt = $pdo->prepare('INSERT INTO enfant (Nom, Prenom, Age, niveau, Tel_urgence, Etat_financier, ID_groupes, photo) VALUES (:nom, :prenom, :age, :niveau, :tel_urgence, :etat_financier, :id_groupes, :photo)');
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':age', $age);
            $stmt->bindParam(':niveau', $niveau);
            $stmt->bindParam(':tel_urgence', $tel_urgence);
            $stmt->bindParam(':etat_financier', $etat_financier);
            $stmt->bindParam(':id_groupes', $id_groupes);
            $stmt->bindParam(':photo', $photo);

            if ($stmt->execute()) {
                echo '<script>M.toast({html: "Enfant créé avec succès !", classes: "rounded"});</script>';
            } else {
                echo '<script>M.toast({html: "Erreur lors de la création de l\'enfant.", classes: "rounded"});</script>';
            }
        }
    } catch (PDOException $e) {
        echo '<script>M.toast({html: "Erreur : ' . $e->getMessage() . '", classes: "rounded"});</script>';
    }
}
?>

</body>
</html>
