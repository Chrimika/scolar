<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=shoolar', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $dateCourante = date('Y-m-d');

    // Compter les arrivées
    $stmt = $pdo->prepare('SELECT COUNT(*) as arrivees FROM présence WHERE DATE(heure_arrivé) = :date');
    $stmt->bindParam(':date', $dateCourante);
    $stmt->execute();
    $arrivees = $stmt->fetch(PDO::FETCH_ASSOC)['arrivees'];

    // Compter les départs, en excluant les heures de départ égales à 00:00:00
    $stmt = $pdo->prepare('SELECT COUNT(*) as departs FROM présence WHERE DATE(heure_depart) = :date AND heure_depart != "00:00:00"');
    $stmt->bindParam(':date', $dateCourante);
    $stmt->execute();
    $departs = $stmt->fetch(PDO::FETCH_ASSOC)['departs'];

    echo json_encode(['arrivees' => $arrivees, 'departs' => $departs]);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
