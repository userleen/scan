<?php
session_start();
include 'dc.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <h2>User Login</h2>
    <?php if(isset($error)) echo $error; ?>
    <form method="post" action="login.php">
         <input type="text" name="username"  placeholder="Username" required><br>
         <input type="password" name="password" placeholder="Password" required><br>
         <input type="submit" value="Login">
         <div id="googleSignIn">
            <button id="googleSignInButton" onclick="googleSignIn()">Sign in with Google</button>
        </div>
    </div>

    </form>
    <script>
        var firebaseConfig = {
            apiKey: "AIzaSyDAB22To7TN98Pd0ikldfA-ckW-A7rHsOw",
            authDomain: "sign-d7bff.firebaseapp.com",
            projectId: "sign-d7bff",
            storageBucket: "Ysign-d7bff.appspot.com",
            messagingSenderId: "369636160328",
            appId: "1:369636160328:web:6ce098f670602e55d4b97d"
        };

        firebase.initializeApp(firebaseConfig);

        function googleSignIn() {
            var provider = new firebase.auth.GoogleAuthProvider();
            provider.setCustomParameters({prompt: 'select_account'});
            firebase.auth().signInWithPopup(provider).then(function(result) {
                // This gives you a Google Access Token. You can use it to access Google API.
                var token = result.credential.accessToken;
                // The signed-in user info.
                var user = result.user;

                // Fill the registration form with Google user's info
                document.querySelector('input[name="username"]').value = user.displayName.replace(/\s+/g, '').toLowerCase();
                document.querySelector('input[name="firstname"]').value = user.displayName.split(' ')[0];
                document.querySelector('input[name="lastname"]').value = user.displayName.split(' ').slice(1).join(' ');
                document.querySelector('input[name="email"]').value = user.email;
            }).catch(function(error) {
                // Handle Errors here.
                var errorCode = error.code;
                var errorMessage = error.message;
                // The email of the user's account used.
                var email = error.email;
                // The firebase.auth.AuthCredential type that was used.
                var credential = error.credential;
                console.error(error);
            });
        }
    </script>
</body>
</html>