<?php use App\KernelFoundation\Security; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Infinite Measures | Dashboard </title>
    <link rel="stylesheet" type="text/css" href="/css/dashboard.css">
    <link rel="stylesheet" type="text/css" href="/css/faq.css">
    <link rel="stylesheet" type="text/css" href="/css/candidate.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://kit.fontawesome.com/f170de025b.js" crossorigin="anonymous"></script>
</head>
<body>
    <header>
        <div class="header-left">
            <input class="burger" type="checkbox"
                   onClick="document.getElementById('sidebar').classList.toggle('hidden')"
            />
            <img src="/img/logo.png" height="50"/>
        </div>
        <div class="header-right">
            <span class="user-name"> Auto école de Castres </span>
            <div class="user-img">
                v <img src=""/>
            </div>
        </div>
    </header>
    <div class="wrapper">
        <div id="sidebar">
            <a href="/">
                <i class="material-icons">home</i> Home
            </a>
            <?php
            if(Security::hasPermission("ROLE_CLIENT")) { ?>
                <a href="#">
                    <i class="material-icons">settings_remote</i> Effectuer une mesure
                </a>
                <a href="#">
                    <i class="material-icons">backup</i> Consulter les mesures
                </a>
                <a href="#">
                    <i class="material-icons">error_outline</i> Ouvrir un ticket
                </a>
                <a href="/client/faq">
                    <i class="material-icons">question_answer</i> Consulter la FAQ
                </a>
                <a href="#/client/candidate">
                    <i class="material-icons">group</i> Consulter les candidats
                </a>
            <?php }?>
            <?php if(Security::hasPermission("ROLE_ADMIN")) { ?>
                <a href="/admin/ticket">
                    <i class="material-icons">error_outline</i> Gérer les tickets
                </a>
                <a href="/admin/faq">
                    <i class="material-icons">question_answer</i> Consulter la FAQ
                </a>
                <a href="/admin/client">
                    <i class="material-icons">group</i> Consulter les clients
                </a>
            <?php }?>
            <?php if(Security::hasPermission("ROLE_SUPER_ADMIN")) {?>
                <a href="/admin/user">
                    <i class="material-icons">group</i> Gérer les administrateurs
                </a>
            <?php }?>
        </div>
        <div class="content">
            <h1 class="content-title"><?= $title ?? "" ?></h1>
            <?= $body  ?>
        </div>
    </div>

    <script src="/js/main.js"></script>
</body>
</html>
