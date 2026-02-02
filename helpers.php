<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start(); // SESSÃO iniciada sempre no topo

function ensureLoggedIn($tipoEsperado = null) {
    if (!isset($_SESSION['id_login'])) {
        header("Location: login.php");
        exit;
    }
    if ($tipoEsperado && $_SESSION['tipo'] !== $tipoEsperado) {
        header("Location: login.php");
        exit;
    }
    return $_SESSION['id_login'];
}