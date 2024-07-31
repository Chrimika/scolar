<!DOCTYPE html>
<html>
<head>
    <title>Seed - Création d'une Séance</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <style>
        .nav-wrapper {
            background-color: #0096D6; /* Bleu ciel plus foncé */
        }
        .right-align-button {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .disabled-link {
            cursor: not-allowed;
            pointer-events: none;
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
    <h2>Créer une Séance</h2>
    <form method="POST" action="seances.php">
        <div class="input-field">
            <input id="titre" name="titre" type="text" class="validate" required>
            <label for="titre">Intitulé</label>
        </div>
        <div class="input-field">
            <input id="duree" name="duree" type="text" class="validate" placeholder="HH:MM" required>
            <label for="duree">Durée (HH:MM)</label>
        </div>
        <div class="input-field">
            <input id="encadreur" name="encadreur" type="text" class="validate" required>
            <label for="encadreur">Encadreur</label>
        </div>
        <div class="input-field">
            <label for="date">Date</label>
            <input id="date" name="date" type="text" class="validate" disabled>
        </div>
        <button class="btn waves-effect waves-light" type="submit" name="action">Créer
            <i class="material-icons right">send</i>
        </button>
    </form>

    <div class="right-align-button">
        <a id="manage-attendance" href="presence.php" class="btn waves-effect waves-light">Gérer les Présences</a>
    </div>

    <?php
    $sessionCreated = false;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $titre = $_POST['titre'];
        $duree = $_POST['duree'];
        $encadreur = $_POST['encadreur'];

        // Connexion à la base de données
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=shoolar', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Date courante
            $dateCourante = date('Y-m-d'); // Format YYYY-MM-DD

            // Vérifier si une séance existe déjà pour la date courante
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM seance WHERE date = :date');
            $stmt->bindParam(':date', $dateCourante);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        M.toast({html: "Une séance existe déjà pour aujourd\'hui.", classes: "rounded"});
                    });
                </script>';
            } else {
                // Préparer et exécuter l'insertion dans la base de données
                $stmt = $pdo->prepare('INSERT INTO seance (intitulé, duree, encadreur, date) VALUES (:titre, :duree, :encadreur, :date)');
                $stmt->bindParam(':titre', $titre);
                $stmt->bindParam(':duree', $duree);
                $stmt->bindParam(':encadreur', $encadreur);
                $stmt->bindParam(':date', $dateCourante);

                if ($stmt->execute()) {
                    $sessionCreated = true;
                    echo '<script>
                        document.addEventListener("DOMContentLoaded", function() {
                            M.toast({html: "Séance créée avec succès !", classes: "rounded"});
                        });
                    </script>';
                } else {
                    echo '<script>
                        document.addEventListener("DOMContentLoaded", function() {
                            M.toast({html: "Erreur lors de la création de la séance.", classes: "rounded"});
                        });
                    </script>';
                }
            }
        } catch (PDOException $e) {
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    M.toast({html: "Erreur : ' . $e->getMessage() . '", classes: "rounded"});
                });
            </script>';
        }
    }

    // Check if there is at least one session
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=shoolar', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->query('SELECT COUNT(*) FROM seance');
        $sessionCount = $stmt->fetchColumn();

        if ($sessionCount == 0) {
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    var manageAttendanceButton = document.getElementById("manage-attendance");
                    manageAttendanceButton.classList.add("disabled-link");
                    manageAttendanceButton.addEventListener("click", function(event) {
                        event.preventDefault();
                        M.toast({html: "Veuillez d\'abord créer une séance pour gérer les présences.", classes: "rounded"});
                    });
                });
            </script>';
        }
    } catch (PDOException $e) {
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                M.toast({html: "Erreur lors de la vérification des séances : ' . $e->getMessage() . '", classes: "rounded"});
            });
        </script>';
    }
    ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('date').value = new Date().toISOString().split('T')[0]; // Format YYYY-MM-DD
    });
</script>

</body>
</html>
