<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['audio']) && isset($_POST['ID_enfant']) && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_voice') {
        $audio = $_FILES['audio'];
        $ID_enfant = $_POST['ID_enfant'];

        if ($audio['error'] === UPLOAD_ERR_OK) {
            $audioName = uniqid() . '.wav';
            $audioPath = 'uploads/' . $audioName;

            if (move_uploaded_file($audio['tmp_name'], $audioPath)) {
                try {
                    $pdo = new PDO('mysql:host=localhost;dbname=shoolar', 'root', '');
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    $signature_voix = $audioName;
                    $current_date = date('Y-m-d');

                    $stmt = $pdo->prepare('UPDATE présence SET signature_voix = :signature_voix WHERE ID_enfant = :ID_enfant AND ID_seance = (SELECT ID_seance FROM seance WHERE date = :date)');
                    $stmt->bindParam(':signature_voix', $signature_voix);
                    $stmt->bindParam(':ID_enfant', $ID_enfant);
                    $stmt->bindParam(':date', $current_date);
                    $stmt->execute();

                    echo 'Enregistrement vocal sauvegardé avec succès.';
                } catch (PDOException $e) {
                    echo 'Erreur : ' . $e->getMessage();
                }
            } else {
                echo 'Erreur lors du téléchargement du fichier audio.';
            }
        } else {
            echo 'Erreur de fichier audio.';
        }
    } else {
        echo 'Paramètres manquants.';
    }
} else {
    echo 'Requête non valide.';
}
?>
