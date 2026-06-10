<?php
require_once "includes/global.php";

if (isset($_POST["email"]) && isset($_POST["password"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    try {
        $authentificator->processAuth($email, $password);
    } catch (Exception $e) {
        echo "General Error: " . $e->getMessage();
    }
}

if (isset($_POST["dev"])) {
    $password = $_POST["password"];
    var_dump($authentificator->hash_password($password));
}

if(isset($_POST["logout"])) {
    $authentificator->logout();
}

var_dump($_SESSION);

?>

<form method="post">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>

<?php
if ($authentificator->isLoggedIn()) {
    echo "Logged in as: " . $session->getUserSession()['CLI_PRENOM'] . " " . $session->getUserSession()['CLI_NOM'];
}
?>

<button id="logout">Logout</button>
<button id="dev">DEV</button>

<script>
    document.getElementById("logout").addEventListener("click", function() {
        fetch("", {
            method: "POST",
            body: new URLSearchParams({
                logout: "1"
            })
        }).then(() => {
            location.reload();
        })
    });

    document.getElementById("dev").addEventListener("click", function() {
        const password = prompt("Enter password to hash:");
        if (password) {
            const formData = new FormData();
            formData.append("password", password);
            formData.append("dev", "1");

            fetch("", {
                    method: "POST",
                    body: formData
                }).then(response => response.text())
                .then(data => {
                    console.log(data);

                    alert("Hashed password: " + data);
                })
        }
    });
</script>