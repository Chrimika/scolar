<!DOCTYPE html>
<html>
<head>
    <title>Seed - Liste des Enfants</title>
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
            justify-content: center;
        }
        .action-buttons a {
            margin: 0 10px;
        }
        .fixed-action-btn {
            position: fixed;
            bottom: 45px;
            right: 24px;
            z-index: 999;
        }
        .fixed-action-btn a {
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
    <h2>Liste des Enfants</h2>

    <!-- Affichage des messages -->
    <?php
    session_start();
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $message_type = $_SESSION['message_type'];
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                M.toast({html: '$message', classes: 'rounded $message_type'});
            });
        </script>";
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
    ?>

    <div class="input-field col s12">
        <select id="groupe-filter">
            <option value="" disabled selected>Choisir un groupe</option>
            <?php
            // Connexion à la base de données
            try {
                $pdo = new PDO('mysql:host=localhost;dbname=shoolar', 'root', '');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Récupérer tous les groupes
                $stmt = $pdo->prepare('SELECT * FROM groupe');
                $stmt->execute();
                $groupes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($groupes as $groupe) {
                    echo '<option value="' . htmlspecialchars($groupe['ID_groupe']) . '">' . htmlspecialchars($groupe['Nom']) . '</option>';
                }
            } catch (PDOException $e) {
                echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        M.toast({html: "Erreur : ' . $e->getMessage() . '", classes: "rounded error"});
                    });
                </script>';
            }
            ?>
        </select>
        <label>Filtrer par groupe</label>
    </div>

    <table class="highlight centered">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Âge</th>
                <th>Niveau</th>
                <th>Tél. Urgence</th>
                <th>État Financier</th>
                <th>Groupe</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="enfants-table">
            <?php
            // Fonction pour afficher les enfants
            function afficherEnfants($pdo, $idGroupe = null) {
                $sql = 'SELECT enfant.*, groupe.numero AS num_groupe FROM enfant LEFT JOIN groupe ON enfant.ID_groupes = groupe.ID_groupes';
                if ($idGroupe) {
                    $sql .= ' WHERE enfant.ID_groupes = :id_groupe';
                }
                $sql .= ' ORDER BY enfant.Nom ASC';

                $stmt = $pdo->prepare($sql);
                if ($idGroupe) {
                    $stmt->bindParam(':id_groupe', $idGroupe);
                }
                $stmt->execute();
                $enfants = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($enfants as $enfant) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($enfant['Nom']) . '</td>';
                    echo '<td>' . htmlspecialchars($enfant['Prenom']) . '</td>';
                    echo '<td>' . htmlspecialchars($enfant['Age']) . '</td>';
                    echo '<td>' . htmlspecialchars($enfant['niveau']) . '</td>';
                    echo '<td>' . htmlspecialchars($enfant['Tel_urgence']) . '</td>';
                    echo '<td>' . htmlspecialchars($enfant['Etat_financier']) . '</td>';
                    echo '<td>' . htmlspecialchars($enfant['num_groupe']) . '</td>';
                    echo '<td class="action-buttons">
                            <a href="#edit-modal" class="btn-floating btn-small waves-effect waves-light green modal-trigger" data-id="' . htmlspecialchars($enfant['ID_enfant']) . '"><i class="material-icons">edit</i></a>
                            <a href="#delete-modal" class="btn-floating btn-small waves-effect waves-light red modal-trigger" data-id="' . htmlspecialchars($enfant['ID_enfant']) . '"><i class="material-icons">delete</i></a>
                          </td>';
                    echo '</tr>';
                }
            }

            // Connexion à la base de données
            try {
                $pdo = new PDO('mysql:host=localhost;dbname=shoolar', 'root', '');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Afficher les enfants sans filtre initialement
                afficherEnfants($pdo);

            } catch (PDOException $e) {
                echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        M.toast({html: "Erreur : ' . $e->getMessage() . '", classes: "rounded error"});
                    });
                </script>';
            }
            ?>
        </tbody>
    </table>

    <!-- Fixed action button -->
    <div class="fixed-action-btn">
        <a href="enfants_ajout.php" class="btn-floating btn-large waves-effect waves-light blue">
            <i class="material-icons">add</i>
        </a>
    </div>

    <!-- Modals -->
    <!-- Modal for Delete Confirmation -->
    <div id="delete-modal" class="modal">
        <div class="modal-content">
            <h4>Confirmation de Suppression</h4>
            <p>Êtes-vous sûr de vouloir supprimer cet enfant ?</p>
        </div>
        <div class="modal-footer">
            <a href="#!" class="modal-close waves-effect waves-red btn-flat">Annuler</a>
            <a href="#!" id="confirm-delete" class="modal-close waves-effect waves-green btn-flat">Supprimer</a>
        </div>
    </div>

    <!-- Modal for Edit -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <h4>Modifier Enfant</h4>
            <p>Chargement des informations de l'enfant...</p>
        </div>
        <div class="modal-footer">
            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Annuler</a>
            <a href="#!" class="modal-close waves-effect waves-green btn-flat">Sauvegarder</a>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script>
    $(document).ready(function() {
        $('select').formSelect();
        $('.modal').modal();

        $('#groupe-filter').change(function() {
            var idGroupe = $(this).val();
            $.ajax({
                url: 'enfants_filtrer.php',
                method: 'GET',
                data: { id_groupe: idGroupe },
                success: function(response) {
                    $('#enfants-table').html(response);
                }
            });
        });

        $('.modal-trigger').on('click', function() {
            var idEnfant = $(this).data('id');
            if ($(this).hasClass('red')) {
                $('#confirm-delete').data('id', idEnfant);
            } else if ($(this).hasClass('green')) {
                // Logique pour charger les données de l'enfant pour l'édition
                // TODO: Ajouter le code pour charger et afficher les données de l'enfant dans le formulaire de modification
            }
        });

        $('#confirm-delete').on('click', function() {
            var idEnfant = $(this).data('id');
            window.location.href = 'enfants_action.php?action=supprimer&id=' + idEnfant;
        });
    });
</script>
</body>
</html>
