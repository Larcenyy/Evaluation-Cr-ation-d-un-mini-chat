<?php
    session_start();
    if (isset($_SESSION["authenticated"]) && $_SESSION["authenticated"]) {
        require "./Classe/DbPDO.php";
        DbPDO::connect();
    }
    else{
        header('Location: login.php');
    }

    if (isset($_POST["sendMessage"])){
        DbPDO::addMessage();
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>MINI CHAT - EVAL</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
          integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <meta name="viewport" content="width-device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../libs/style/main.css">
</head>
<body>
    <nav>
        <ul>
            <li><a href="#" ><i class="fas fa-home"></i> Accueil</a></li>
            <li><a href="logout.php" id="login"><i class="fas fa-sign-out-alt"></i> Se déconnecter</a></li>
        </ul>
    </nav>
    <main>
        <section>
            <div class="container">
                <div class="content">
                    <div class="chat-content">
                        <div class="chat-left">
                            <?php DbPDO::getMessage(); ?>
                        </div>
                    </div>
                    <div class="content-footer">
                        <form action="#" method="post">
                            <input id="message" name="message" type="text" placeholder="Votre message.." minlength="1" maxlength="100" required>
                            <input id="submit" type="submit" value="Envoyer" name="sendMessage">
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script src="../libs/app.js"></script>
</body>
</html>