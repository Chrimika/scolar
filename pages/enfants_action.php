<?php
// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=shoolar', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
        $idEnfant = $_GET['id'];

        // Préparer la requête de suppression
        $stmt = $pdo->prepare('DELETE FROM enfant WHERE ID_enfant = :id_enfant');
        $stmt->bindParam(':id_enfant', $idEnfant);

        // Essayer de supprimer l'enfant
        try {
            $stmt->execute();
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    M.toast({html: "Enfant supprimé avec succès", classes: "rounded"});
                    // Recharger la page après la suppression
                    setTimeout(function() {
                        window.location.href = "enfants.php";
                    }, 1500);
                });
            </script>';
        } catch (PDOException $e) {
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    M.toast({html: "Erreur lors de la suppression : ' . $e->getMessage() . '", classes: "rounded"});
                });
            </script>';
        }
    }
} catch (PDOException $e) {
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            M.toast({html: "Erreur de connexion : ' . $e->getMessage() . '", classes: "rounded"});
        });
    </script>';
}
?>
