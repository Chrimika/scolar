<!DOCTYPE html>
<html>
<head>
    <title>Seed - Gestion des Présences</title>
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
        .btn-margin {
            margin: 0 5px;
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
    <h2>Gestion des Présences</h2>
    <?php
    // Connexion à la base de données
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=shoolar', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupérer l'ID de la séance depuis l'URL
        $seanceId = isset($_GET['seance_id']) ? intval($_GET['seance_id']) : 0;

        // Vérifier si la séance existe
        $stmt = $pdo->prepare('SELECT * FROM seance WHERE id = :id');
        $stmt->bindParam(':id', $seanceId);
        $stmt->execute();
        $seance = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$seance) {
            echo '<script>M.toast({html: "Séance non trouvée.", classes: "rounded"});</script>';
            exit;
        }

        // Récupérer tous les enfants
        $stmt = $pdo->prepare('SELECT * FROM enfant');
        $stmt->execute();
        $enfants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Vérifier les présences existantes
        $stmt = $pdo->prepare('SELECT enfant_id FROM presence WHERE seance_id = :seance_id');
        $stmt->bindParam(':seance_id', $seanceId);
        $stmt->execute();
        $presentEnfants = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Afficher la liste des enfants avec des cases à cocher
        echo '<form method="POST" action="presence.php">';
        echo '<input type="hidden" name="seance_id" value="' . $seanceId . '">';
        foreach ($enfants as $enfant) {
            $checked = in_array($enfant['id'], $presentEnfants) ? 'checked' : '';
            echo '<p>
                    <label>
                        <input type="checkbox" name="enfants[]" value="' . $enfant['id'] . '" ' . $checked . ' />
                        <span>' . htmlspecialchars($enfant['nom']) . ' ' . htmlspecialchars($enfant['prenom']) . '</span>
                    </label>
                  </p>';
        }
        echo '<button class="btn waves-effect waves-light" type="submit" name="action" value="valider">Valider les Présences</button>';
        echo '</form>';

        // Traitement du formulaire
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'valider') {
            $enfantsSelectionnes = isset($_POST['enfants']) ? $_POST['enfants'] : [];
            $seanceId = intval($_POST['seance_id']);
            $dateCourante = date('Y-m-d H:i:s'); // Heure actuelle pour l'arrivée

            try {
                $pdo->beginTransaction();

                // Insérer les présences
                $stmt = $pdo->prepare('INSERT INTO presence (seance_id, enfant_id, heure_arrivee) VALUES (:seance_id, :enfant_id, :heure_arrivee)');
                foreach ($enfantsSelectionnes as $enfantId) {
                    $stmt->bindParam(':seance_id', $seanceId);
                    $stmt->bindParam(':enfant_id', $enfantId);
                    $stmt->bindParam(':heure_arrivee', $dateCourante);
                    $stmt->execute();
                }

                $pdo->commit();
                echo '<script>M.toast({html: "Présences validées avec succès !", classes: "rounded"});</script>';

                // Rafraîchir la page pour afficher les boutons de départ
                echo '<script>location.reload();</script>';
            } catch (PDOException $e) {
                $pdo->rollBack();
                echo '<script>M.toast({html: "Erreur : ' . $e->getMessage() . '", classes: "rounded"});</script>';
            }
        }

        // Afficher les boutons de départ si des présences ont été validées
        $stmt = $pdo->prepare('SELECT * FROM presence WHERE seance_id = :seance_id AND heure_arrivee IS NOT NULL AND heure_depart IS NULL');
        $stmt->bindParam(':seance_id', $seanceId);
        $stmt->execute();
        $presences = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($presences)) {
            echo '<h3>Heure de Départ</h3>';
            foreach ($presences as $presence) {
                $enfantId = $presence['enfant_id'];
                $stmt = $pdo->prepare('SELECT * FROM enfant WHERE id = :id');
                $stmt->bindParam(':id', $enfantId);
                $stmt->execute();
                $enfant = $stmt->fetch(PDO::FETCH_ASSOC);

                echo '<p>
                        ' . htmlspecialchars($enfant['nom']) . ' ' . htmlspecialchars($enfant['prenom']) . '
                        <form method="POST" action="presence.php" style="display: inline;">
                            <input type="hidden" name="presence_id" value="' . $presence['id'] . '">
                            <button class="btn waves-effect waves-light btn-margin" type="submit" name="action" value="depart">Départ</button>
                        </form>
                      </p>';
            }

            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'depart') {
                $presenceId = intval($_POST['presence_id']);
                $heureDepart = date('Y-m-d H:i:s'); // Heure actuelle pour le départ

                try {
                    $stmt = $pdo->prepare('UPDATE presence SET heure_depart = :heure_depart WHERE id = :id');
                    $stmt->bindParam(':heure_depart', $heureDepart);
                    $stmt->bindParam(':id', $presenceId);

                    if ($stmt->execute()) {
                        echo '<script>M.toast({html: "Heure de départ enregistrée avec succès !", classes: "rounded"});</script>';
                        echo '<script>location.reload();</script>';
                    } else {
                        echo '<script>M.toast({html: "Erreur lors de l\'enregistrement de l\'heure de départ.", classes: "rounded"});</script>';
                    }
                } catch (PDOException $e) {
                    echo '<script>M.toast({html: "Erreur : ' . $e->getMessage() . '", classes: "rounded"});</script>';
                }
            }
        }
    } catch (PDOException $e) {
        echo '<script>M.toast({html: "Erreur : ' . $e->getMessage() . '", classes: "rounded"});</script>';
    }
    ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var elems = document.querySelectorAll('select');
        M.FormSelect.init(elems);
    });
</script>

</body>
</html>
