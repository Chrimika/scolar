<?php
if (isset($_GET['action']) && isset($_GET['ID_enfant'])) {
    $action = $_GET['action'];
    $ID_enfant = (int)$_GET['ID_enfant'];
    $dateCourante = date('Y-m-d');

    try {
        $pdo = new PDO('mysql:host=localhost;dbname=shoolar', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Vérifier la présence d'une séance pour aujourd'hui
        $stmt = $pdo->prepare('SELECT ID_seance FROM seance WHERE date = :date');
        $stmt->bindParam(':date', $dateCourante);
        $stmt->execute();
        $seance = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($seance) {
            $ID_seance = $seance['ID_seance'];

            if ($action == 'arrivee') {
                // Vérifier si l'enfant est déjà arrivé
                $stmt = $pdo->prepare('SELECT * FROM présence WHERE ID_enfant = :ID_enfant AND ID_seance = :ID_seance');
                $stmt->bindParam(':ID_enfant', $ID_enfant);
                $stmt->bindParam(':ID_seance', $ID_seance);
                $stmt->execute();
                $presence = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$presence) {
                    // Ajouter une entrée pour l'arrivée
                    $stmt = $pdo->prepare('INSERT INTO présence (heure_arrivé, ID_enfant, ID_seance) VALUES (CURRENT_TIME(), :ID_enfant, :ID_seance)');
                    $stmt->bindParam(':ID_enfant', $ID_enfant);
                    $stmt->bindParam(':ID_seance', $ID_seance);
                    $stmt->execute();

                    echo 'Arrivée enregistrée avec succès.';
                } else {
                    echo 'L\'enfant est déjà arrivé.';
                }
            } elseif ($action == 'depart') {
                // Vérifier si l'enfant est arrivé
                $stmt = $pdo->prepare('SELECT * FROM présence WHERE ID_enfant = :ID_enfant AND ID_seance = :ID_seance');
                $stmt->bindParam(':ID_enfant', $ID_enfant);
                $stmt->bindParam(':ID_seance', $ID_seance);
                $stmt->execute();
                $presence = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($presence && $presence['heure_depart'] == '00:00:00') {
                    // Mettre à jour l'heure de départ
                    $stmt = $pdo->prepare('UPDATE présence SET heure_depart = CURRENT_TIME() WHERE ID_enfant = :ID_enfant AND ID_seance = :ID_seance');
                    $stmt->bindParam(':ID_enfant', $ID_enfant);
                    $stmt->bindParam(':ID_seance', $ID_seance);
                    $stmt->execute();

                    echo 'Départ enregistré avec succès.';
                } else {
                    echo $presence ? 'L\'enfant n\'est pas encore arrivé ou est déjà parti.' : 'L\'enfant n\'a pas encore arrivé.';
                }
            } else {
                echo 'Action non reconnue.';
            }
        } else {
            echo 'Aucune séance n\'est prévue pour aujourd\'hui.';
        }
    } catch (PDOException $e) {
        echo 'Erreur : ' . $e->getMessage();
    }
} else {
    echo 'Paramètres manquants.';
}
?>


