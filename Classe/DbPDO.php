<?php

class DbPDO
{
    private static string $server = 'localhost';
    private static string $username = 'root';
    private static string $password = '';
    private static string $database = 'mini-chat';
    private static ?PDO $db = null;

    public static function connect(): ?PDO {
        if (self::$db == null){
            try {
                self::$db = new PDO("mysql:host=".self::$server.";dbname=".self::$database, self::$username, self::$password);
                self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                echo "Erreur de la connexion à la dn : " . $e->getMessage();
                die();
            }
        }
        return self::$db;
    }

    public static function checkLogin(): void
    {
        $req = self::$db->prepare('SELECT id, password, is_online FROM users WHERE pseudo = :username');

        $username = strip_tags($_POST['username'] ?? ''); // Supprime toutes les balises HTML potentiellement dangereuses
        $pass_form = strip_tags($_POST['password'] ?? ''); // Récupère le mot de passe entré dans le formulaire et supprime les balises HTML potentiellement dangereuses

        $req->bindParam(':username', $username);

        $pass_form = strip_tags($pass_form); // Supprime les balises HTML et PHP
        password_hash($pass_form, PASSWORD_BCRYPT); // Step 2 on le filtre

        if ($username && $pass_form) { // Check si les champs on était trouvé
            if ($req->execute()) {
                $userData = $req->fetch(); // Met notre $req en tableau associatif
                if (!empty($userData)) { // Va check si c'est vrai
                    if (password_verify($pass_form, $userData['password'])) { // Check si le mot de passe en clair > filtrer et égal aux mot de passe enregistrer dans la bdd
                        session_start();
                        $id = $userData['id']; // Récupère l'ID de l'utilisateur


                        $_SESSION["authenticated"] = true;
                        $_SESSION['user_id'] = $id;


                        // Vérifier si la connexion est établie avant d'appeler getName()
                        $request = self::$db->prepare('UPDATE users SET is_online = 1 WHERE id = :id');
                        $request->bindParam(':id', $id);
                        $request->execute();

                        header('Location: ../public/index.php');
                    } else {
                        echo("<div class='warning'> Mot de passe incorrect.. </div>");
                        echo "<script>setTimeout(function(){ document.querySelector('.warning').style.display = 'none'; }, 3000);</script>";
                    }
                } else {
                    echo "<div class='warning'> Aucun utilisateur trouvé avec le nom d'utilisateur: " . $username . "</div>";
                    echo "<script>setTimeout(function(){ document.querySelector('.warning').style.display = 'none'; }, 3000);</script>";
                }
            } else {
                echo "<div class='warning'> Aucun compte associé à ce nom d'utilisateur </div>";
                echo "<script>setTimeout(function(){ document.querySelector('.warning').style.display = 'none'; }, 3000);</script>";
            }
        } else {
            echo "<div class='warning'> Aucun champ trouvé..  </div>";
            echo "<script>setTimeout(function(){ document.querySelector('.warning').style.display = 'none'; }, 3000);</script>";
        }
    }


    public static function registerUser(): void
    {
        if (isset($_POST['register-username']) && isset($_POST['register-password']) && isset($_POST['password-repeat'])) {
            $username = strip_tags($_POST['register-username']);
            // Vérifier si l'email est valide
            // Récupère la date d'inscriptions
            $dt = new \DateTime();
            $date = $dt->format("Y-m-d H:i:s");

            // Vérifier si le nom d'utilisateur existe déjà
            $req = self::$db->prepare('SELECT id FROM users WHERE pseudo = :username');
            $req->bindParam(':username', $username);
            $req->execute();
            $result = $req->fetch();
            if ($result) {
                echo "<div class='warning'> Nom d'utilisateur déjà pris. </div>";
                echo "<script>setTimeout(function(){ document.querySelector('.warning').style.display = 'none'; }, 2000);</script>";
                return;
            }

            // Vérifier les deux mots de passe
            if ($_POST['register-password'] === $_POST['password-repeat']){
                $username = htmlspecialchars($_POST['register-username'], ENT_QUOTES); // Convertir les caractères spéciaux en entités HTML
                // Les vérifications ont été passées, on peut ajouter l'utilisateur à la base de données
                $pass = $_POST['register-password'];
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                $req = self::$db->prepare('INSERT INTO users (pseudo, password) VALUES (:username, :password)');
                $req->bindParam(':username', $username);
                $req->bindParam(':password', $hash);
                $req->execute();

                echo "<div class='good'> Vous vous êtes inscrit. </div>";
                echo "<script>setTimeout(function(){ document.querySelector('.good').style.display = 'none'; }, 2000);</script>";
            }
            else{
                echo "<div class='warning'> Mot de passe non identique. </div>";
                echo "<script>setTimeout(function(){ document.querySelector('.warning').style.display = 'none'; }, 2000);</script>";
            }
        }
    }

    public static function logoutUser(): void
    {
        session_start();
        if (isset($_SESSION["authenticated"]) && $_SESSION["authenticated"]) {
            self::connect(); // Vérifie que $db est initialisée
            $req = self::$db->prepare('UPDATE users SET is_online = 0 WHERE id = :id');
            $id = $_SESSION['user_id'];
            $req->bindParam(':id', $id);
            $req->execute();

            $_SESSION["authenticated"] = false;
            $_SESSION = array();
            session_destroy();

            header('Location: ./login.php');
            exit;
        }
        else {
            echo "Vous n'êtes pas connecté à une session";
        }
    }

    public static function addMessage(): void
    {
        if (isset($_SESSION["authenticated"]) && $_SESSION["authenticated"]) {
            if (isset($_POST['message'])){
                $req = self::$db->prepare('INSERT INTO message(message, date, users_id) VALUES (:message, :date, :id)');

                $id = $_SESSION['user_id'];

                $message = strip_tags($_POST['message'] ?? ''); // Supprime toutes les balises HTML potentiellement dangereuses

                $dt = new \DateTime();
                $date = $dt->format("Y-m-d H:i:s");

                $req->bindParam(':id', $id);
                $req->bindParam(':message', $message);
                $req->bindParam(':date', $date);
                $req->execute();
                header('Location: index.php');
            }
        }
        else {
            echo "Vous n'êtes pas connecté à une session";
        }
    }

    public static function getMessage(): void
    {
        if (isset($_SESSION["authenticated"]) && $_SESSION["authenticated"]) {
            $req = self::$db->prepare('SELECT mess.message, users.is_online, users.pseudo, mess.date FROM message as mess
            INNER JOIN users as users ON mess.users_id = users.id');

            $check = $req->execute();
            if ($check){
                foreach ($req as $value){
                    $color = $value['is_online'] == 1 ? "green" : "red";
                    echo "<div class='showBox'>";
                        echo "<div>";
                            echo "<span> <i class='fas fa-circle' style='color: $color;'></i> " . $value['pseudo'] .  "</span>";
                            echo "<span> <i class='fas fa-clock'></i> " . $value['date'] . "</span>";
                        echo "</div>";
                        echo "<p>" . $value['message'] . "</p>";
                    echo "</div>";
                }
            }
        }
        else {
            echo "Vous n'êtes pas connecté à une session";
        }
    }

    public static function apiTest(): void
    {
        $request_method = $_SERVER["REQUEST_METHOD"];
        switch($request_method)
        {
            case 'GET':
                if(!empty($_GET["id"]))
                {
                    // Récupérer un seul produit
                    $id = intval($_GET["id"]);
                    DbPDO::getProducts($id);
                }
                else
                {
                    // Récupérer tous les produits
                    DbPDO::getProducts();
                }
                break;
            default:
                // Requête invalide
                header("HTTP/1.0 405 Method Not Allowed");
                break;
        }
    }



    /* Fonction qui permet de récupèrer les message qui le met en tableau associatif
       Ensuite ce tableau est converti pour JSON
       Et ensuite devient un objet JSON
    */

    public static function getProducts(): void
    {
        $stmt = self::$db->prepare('SELECT * FROM message WHERE DATE >= :date');

        $date = date("Y-m-d H:i:s", time() - 3);


        $stmt->bindParam(":date", $date);
        $stmt->execute();

        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

}