<?php
session_start();
require_once __DIR__ . '/database.php';

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT id, nom, prenom, email, role FROM utilisateurs WHERE id = ? AND actif = 1");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function formatMontant(float $montant, string $devise = 'FCFA'): string {
    return number_format($montant, 0, ',', ' ') . ' ' . $devise;
}

function formatDate(?string $date, string $format = 'd/m/Y'): string {
    if (!$date) return '-';
    return date($format, strtotime($date));
}

function generateNumero(string $prefix): string {
    return $prefix . '-' . date('Y') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
}

function badgeStatut(string $statut): string {
    $map = [
        'en_cours'    => ['success',   'En cours'],
        'resilie'     => ['danger',    'Résilié'],
        'expire'      => ['warning',   'Expiré'],
        'paye'        => ['success',   'Payé'],
        'impaye'      => ['danger',    'Impayé'],
        'retard'      => ['danger',    'Retard'],
        'partiel'     => ['warning',   'Partiel'],
        'en_attente'  => ['secondary', 'En attente'],
        'libre'       => ['info',      'Libre'],
        'occupe'      => ['success',   'Occupé'],
        'travaux'     => ['warning',   'Travaux'],
        'actif'       => ['success',   'Actif'],
        'inactif'     => ['secondary', 'Inactif'],
        'liste_noire' => ['dark',      'Liste noire'],
    ];
    $b = $map[$statut] ?? ['secondary', $statut];
    return "<span class='badge bg-{$b[0]}'>{$b[1]}</span>";
}

function setFlash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}