<!DOCTYPE html>
<html>
<head>
    <title>Seed - Création de Groupe</title>
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
    <h2>Créer un Groupe</h2>
    <form method="POST" action="groupes.php">
        <label for="numero" style="font-size: 18px";>Jours de passage</label> 
        <div class="input-field">
            <p>
                <label>
                    <input type="checkbox" name="jours[]" value="Lundi" />
                    <span>Lundi</span>
                </label>
            </p>
            <p>
                <label>
                    <input type="checkbox" name="jours[]" value="Mardi" />
                    <span>Mardi</span>
                </label>
            </p>
            <p>
                <label>
                    <input type="checkbox" name="jours[]" value="Mercredi" />
                    <span>Mercredi</span>
                </label>
            </p>
            <p>
                <label>
                    <input type="checkbox" name="jours[]" value="Jeudi" />
                    <span>Jeudi</span>
                </label>
            </p>
            <p>
                <label>
                    <input type="checkbox" name="jours[]" value="Vendredi" />
                    <span>Vendredi</span>
                </label>
            </p>
            <p>
                <label>
                    <input type="checkbox" name="jours[]" value="Samedi" />
                    <span>Samedi</span>
                </label>
            </p>
        </div>
        <div class="input-field">
            <input id="numero" name="numero" type="number" class="validate" required>
            <label for="numero">Numéro</label>
        </div>
        <button class="btn waves-effect waves-light" type="submit" name="action">Créer
            <i class="material-icons right">send</i>
        </button>
    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $jours = implode(", ", $_POST['jours']);
    $numero = $_POST['numero'];

    try {
        $pdo = new PDO('mysql:host=localhost;dbname=shoolar', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare('INSERT INTO groupe (jours, numero) VALUES (:jours, :numero)');
        $stmt->bindParam(':jours', $jours);
        $stmt->bindParam(':numero', $numero);

        if ($stmt->execute()) {
            echo '<script>M.toast({html: "Groupe créé avec succès !", classes: "rounded"});</script>';
        } else {
            echo '<script>M.toast({html: "Erreur lors de la création du groupe.", classes: "rounded"});</script>';
        }
    } catch (PDOException $e) {
        echo '<script>M.toast({html: "Erreur : ' . $e->getMessage() . '", classes: "rounded"});</script>';
    }
}
?>
</body>
</html>
