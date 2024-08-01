<!DOCTYPE html>
<html>
<head>
    <title>Seed - Gestion des Présences</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <style>
        .nav-wrapper {
            background-color: #0096D6;
        }
        .container {
            margin-top: 30px;
        }
        .action-buttons {
            display: flex;
            justify-content: space-evenly;
            align-items: center;
        }
        .action-buttons button, .action-buttons i {
            margin: 0;
        }
        .disabled-button {
            cursor: not-allowed;
            pointer-events: none;
        }
        td {
            text-align: center;
        }
        .counterA {
            font-size: 1.2em;
            margin-left: 20px;
            color: green;
        }
        .counterD {
            font-size: 1.2em;
            margin-left: 20px;
            color: red;
        }
        .counter span {
            font-weight: bold;
        }
        .entete {
            display: flex;
            justify-content: space-around;
            margin-top: 10px;
        }
        .mic-icon {
            cursor: pointer;
        }
        .recording-animation {
            animation: blink 1s infinite;
        }
        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0; }
            100% { opacity: 1; }
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
    <h2 style="text-align: center;">
        Gérer les Présences <br>
        <div class="entete">
            <span class="counterA">Arrivées: <span id="arrivees-counter">0</span></span>
            <span class="counterD">Départs: <span id="departs-counter">0</span></span>
        </div>
    </h2>
    
    <?php
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=shoolar', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $dateCourante = date('Y-m-d');
        $stmt = $pdo->prepare('SELECT ID_seance FROM seance WHERE date = :date');
        $stmt->bindParam(':date', $dateCourante);
        $stmt->execute();
        $seance = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($seance) {
            $ID_seance = $seance['ID_seance'];

            $stmt = $pdo->prepare('SELECT * FROM enfant');
            $stmt->execute();
            $enfants = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo '<table class="highlight centered">';
            echo '<thead><tr><th>Nom</th><th>Prénom</th><th>Actions</th></tr></thead>';
            echo '<tbody>';

            foreach ($enfants as $enfant) {
                $ID_enfant = $enfant['ID_enfant'];

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
                echo '<i class="material-icons mic-icon ' . $departDisabled . '" onclick="recordVoice(' . $ID_enfant . ')">mic</i>';
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '<!-- Ajouté à la fin du contenu existant dans presence.php -->
                <div style="text-align: center; margin-top: 20px;margin-bottom: 20px;">
                    <a href="liste_presences.php" class="btn waves-effect waves-light">Liste Présences</a>
                </div>
                ';
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
            const toastMessage = sessionStorage.getItem('toastMessage');
            if (toastMessage) {
                M.toast({html: toastMessage, classes: "rounded"});
                sessionStorage.removeItem('toastMessage');
            }

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

        function recordVoice(ID_enfant) {
            navigator.mediaDevices.getUserMedia({ audio: true })
                .then(stream => {
                    const mediaRecorder = new MediaRecorder(stream);
                    const audioChunks = [];

                    mediaRecorder.ondataavailable = event => {
                        audioChunks.push(event.data);
                    };

                    mediaRecorder.onstop = () => {
                        const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                        const formData = new FormData();
                        formData.append('audio', audioBlob);
                        formData.append('ID_enfant', ID_enfant);
                        formData.append('action', 'save_voice');

                        fetch('voice_action.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(result => {
                            sessionStorage.setItem('toastMessage', result);
                            location.reload();
                        });
                    };

                    mediaRecorder.start();
                    document.querySelector('.mic-icon[onclick="recordVoice(' + ID_enfant + ')"]').classList.add('recording-animation');
                    M.toast({html: "-- veillez parler", classes: "rounded"});
                    
                    setTimeout(() => {
                        mediaRecorder.stop();
                        document.querySelector('.mic-icon[onclick="recordVoice(' + ID_enfant + ')"]').classList.remove('recording-animation');
                    }, 10000);
                })
                .catch(error => {
                    console.error('Erreur d\'enregistrement vocal:', error);
                    M.toast({html: "Erreur d'enregistrement vocal", classes: "rounded"});
                });
        }
    </script>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
</body>
</html>
