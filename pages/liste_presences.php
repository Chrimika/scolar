<!DOCTYPE html>
<html>
<head>
    <title>Seed - Liste des Présences</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <style>
        .nav-wrapper {
            background-color: #0096D6;
        }
        .container {
            margin-top: 30px;
        }
        .highlight {
            background-color: #f9f9f9;
        }
        td {
            text-align: center;
        }
        .play-icon, .send-icon {
            cursor: pointer;
            margin-left: 10px;
        }
        .audio-player {
            display: none;
            width: 100%;
            margin-top: 10px;
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
            <li><a href="presence.php">Gérer les Présences</a></li>
            <li><a href="liste_presences.php">Liste Présences</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <h2 style="text-align: center;">Liste des Présences</h2>
    
    <?php
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=shoolar', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare('SELECT * FROM présence
                                JOIN enfant ON présence.ID_enfant = enfant.ID_enfant
                                JOIN seance ON présence.ID_seance = seance.ID_seance');
        $stmt->execute();
        $presences = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($presences) {
            echo '<table class="highlight centered">';
            echo '<thead><tr><th>Nom</th><th>Prénom</th><th>Téléphone</th><th>Date d\'Arrivée</th><th>Date de Départ</th><th>Séance</th><th>Signature</th></tr></thead>';
            echo '<tbody>';

            foreach ($presences as $presence) {
                $signaturePath = 'uploads/' . htmlspecialchars($presence['signature_voix']); // Path to the audio file
                $audioExists = file_exists($signaturePath) ? 'yes' : 'no'; // Check if file exists
                
                // Encode the audio file URL for use in the WhatsApp URL
                $encodedAudioPath = urlencode($signaturePath);
                
                echo '<tr>';
                echo '<td>' . htmlspecialchars($presence['Nom']) . '</td>';
                echo '<td>' . htmlspecialchars($presence['Prenom']) . '</td>';
                echo '<td>' . htmlspecialchars($presence['Tel_urgence']) . '</td>';
                echo '<td>' . htmlspecialchars($presence['heure_arrivé']) . '</td>';
                echo '<td>' . htmlspecialchars($presence['heure_depart']) . '</td>';
                echo '<td>' . htmlspecialchars($presence['intitulé']) . '</td>';
                echo '<td>';
                if ($audioExists === 'yes') {
                    $telUrgence = htmlspecialchars($presence['Tel_urgence']);
                    echo '<i class="material-icons play-icon" onclick="playAudio(\'' . $signaturePath . '\')">play_arrow</i>';
                    echo '<a href="https://wa.me/' . $telUrgence . '?text=Bonjour, voici l\'enregistrement audio pour l\'enfant. ' . $encodedAudioPath . '" target="_blank">';
                    echo '<i class="material-icons send-icon">send</i>';
                    echo '</a>';
                } else {
                    echo 'Aucune signature';
                }
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<div class="card-panel red lighten-3">Aucune présence enregistrée.</div>';
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
        function playAudio(audioSrc) {
            const audioPlayer = document.createElement('audio');
            audioPlayer.src = audioSrc;
            audioPlayer.controls = true;
            audioPlayer.style.display = 'block'; // Show the audio player
            document.body.appendChild(audioPlayer);

            audioPlayer.play();
            
            audioPlayer.onended = () => {
                audioPlayer.style.display = 'none'; // Hide the audio player when playback ends
                document.body.removeChild(audioPlayer); // Remove the audio player from the DOM
            };
        }
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</div>

</body>
</html>
