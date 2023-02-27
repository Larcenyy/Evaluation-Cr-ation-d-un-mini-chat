<?php
    require 'Classe/DbPDO.php';
    DbPDO::connect();

    if (isset($_POST['connect'])){
        DbPDO::checkLogin();
    }
    elseif (isset($_POST['register'])){
        DbPDO::registerUser();
    }
?>

<head>
    <title>CHATLOG | CONNEXION</title>
    <link rel="stylesheet" type="text/css" href="/libs/style/register.css">
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@500&display=swap" rel="stylesheet">
</head>
<body>
<div class="main">
    <input type="checkbox" id="chk" aria-hidden="true">

    <div class="signup">
        <form action="" method="post">
            <label for="chk" aria-hidden="true">Pas inscrit?</label>
            <input type="text" name="register-username" placeholder="Pseudo" required="">
            <input type="password" name="register-password" placeholder="Password" required="">
            <input type="password" name="password-repeat" placeholder="Répété-Password" required="">
            <input id="submit" type="submit" value="Valider" name="register">
        </form>
    </div>

    <div class="login">
        <form action="" method="post">
            <label for="chk" aria-hidden="true">Connexion</label>
            <input type="text" name="username" placeholder="Pseudo" required="">
            <input type="password" name="password" placeholder="Password" required="">
            <input id="submit" type="submit" value="Connexion" name="connect">
        </form>
    </div>
</div>
</body>
