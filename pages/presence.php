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
        .container {
            margin-top: 30px;
        }
        .action-buttons {
            display: flex;
            justify-content: space-evenly; /* Espace égal entre les boutons */
            align-items: center; /* Centrer les boutons verticalement */
        }
        .action-buttons button {
            margin: 0; /* Éviter les marges pour une meilleure alignement */
        }
        .disabled-button {
            cursor: not-allowed;
            pointer-events: none; /* Empêche les clics sur le bouton désactivé */
        }
        td {
            text-align: center; /* Centrer le contenu des cellules pour une meilleure présentation */
        }
        .counter {
            font-size: 1.2em;
            margin-left: 20px;
        }
        .counter span {
            font-weight: bold;
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
    <h2>
        Gérer les Présences
        <span class="counter">Arrivées: <span id="arrivees-counter">0</span></span>
        <span class="counter">Départs: <span id="departs-counter">0</span></span>
    </h2>
    
    <?php
    // Connexion à la base de données
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=shoolar', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Vérifier la présence d'une séance pour aujourd'hui
        $dateCourante = date('Y-m-d');
        $stmt = $pdo->prepare('SELECT ID_seance FROM seance WHERE date = :date');
        $stmt->bindParam(':date', $dateCourante);
        $stmt->execute();
        $seance = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($seance) {
            $ID_seance = $seance['ID_seance'];

            // Récupérer tous les enfants
            $stmt = $pdo->prepare('SELECT * FROM enfant');
            $stmt->execute();
            $enfants = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo '<table class="highlight centered">';
            echo '<thead><tr><th>Nom</th><th>Prénom</th><th>Actions</th></tr></thead>';
            echo '<tbody>';

            foreach ($enfants as $enfant) {
                $ID_enfant = $enfant['ID_enfant'];

                // Vérifier la présence actuelle de l'enfant pour la séance du jour
                $stmt = $pdo->prepare('SELECT * FROM présence WHERE ID_enfant = :ID_enfant AND ID_seance = :ID_seance');
                $stmt->bindParam(':ID_enfant', $ID_enfant);
                $stmt->bindParam(':ID_seance', $ID_seance);
                $stmt->execute();
                $presence = $stmt->fetch(PDO::FETCH_ASSOC);

                $arriveeDisabled = $presence ? 'disabled-button' : '';
                $departDisabled = !$presence ? 'disabled-button' : '';

                echo '<tr>';
                echo '<td>' . htmlspecialchars($enfant['Nom']) . '</td>';
                echo '<td>' . htmlspecialchars($enfant['Prenom']) . '</td>';
                echo '<td class="action-buttons">';
                echo '<button class="btn waves-effect waves-light ' . $arriveeDisabled . '" onclick="arrivee(' . $ID_enfant . ')">Arrivée</button>';
                echo '<button class="btn waves-effect waves-light ' . $departDisabled . '" onclick="depart(' . $ID_enfant . ')">Départ</button>';
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<div class="card-panel red lighten-3">Aucune séance n\'est prévue pour aujourd\'hui.</div>';
        }
    } catch (PDOException $e) {
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                M.toast({html: "Erreur : ' . $e->getMessage() . '", classes: "rounded"});
            });
        </script>';
    }
    ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Afficher les toasts stockés dans sessionStorage
            const toastMessage = sessionStorage.getItem('toastMessage');
            if (toastMessage) {
                M.toast({html: toastMessage, classes: "rounded"});
                sessionStorage.removeItem('toastMessage');
            }

            // Mettre à jour les compteurs
            updateCounters();
        });

        function updateCounters() {
            fetch('presence_counters.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('arrivees-counter').textContent = data.arrivees;
                    document.getElementById('departs-counter').textContent = data.departs;
                });
        }

        function arrivee(ID_enfant) {
            fetch('presence_action.php?action=arrivee&ID_enfant=' + ID_enfant)
                .then(response => response.text())
                .then(result => {
                    sessionStorage.setItem('toastMessage', result);
                    location.reload();
                });
        }

        function depart(ID_enfant) {
            fetch('presence_action.php?action=depart&ID_enfant=' + ID_enfant)
                .then(response => response.text())
                .then(result => {
                    sessionStorage.setItem('toastMessage', result);
                    location.reload();
                });
        }
    </script>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
