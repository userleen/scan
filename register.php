<?php
include 'dc.php';

// Include Firebase SDK in your HTML file
require_once __DIR__ . '/vendor/autoload.php';
use Kreait\Firebase\Factory;

// Your Firebase project configuration
$firebaseConfig = [
    'apiKey' => 'AIzaSyDAB22To7TN98Pd0ikldfA-ckW-A7rHsOw',
    'authDomain' => 'sign-d7bff.firebaseapp.com',
    'projectId' => 'sign-d7bff',
    'storageBucket' => 'Ysign-d7bff.appspot.com',
    'messagingSenderId' => '369636160328',
    'appId' => '1:369636160328:web:6ce098f670602e55d4b97d'
];

// Read the contents of the serviceAccount.json file
$serviceAccount = json_decode(file_get_contents(__DIR__.'/serviceAccount.json'), true);

$firebase = (new Factory)
    ->withServiceAccount($serviceAccount)
    ->withDatabaseUri('https://your-database.firebaseio.com/') // Add your Firebase database URL here
    ->createDatabase();
    

$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $repeat_password = $_POST['repeat_password'];
    $role = $_POST['role'];

    // Password validation
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number    = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);

    if(empty($password)){
        $password_error = "Password is required.";
    }
    elseif(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
        $password_error = "Password must contain at least one uppercase letter, one lowercase letter, one number, one special character, and be at least 8 characters long.";
    }
    elseif ($password != $repeat_password) {
        $password_error = "Passwords do not match.";
    } 
    else {
        // Check if the username or email already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $username_error = "Username or email already exists.";
        } else {
            // Insert user into database with selected role
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, firstname, lastname, email, password, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $firstname, $lastname, $email, $hashed_password, $role]);
            // Redirect to index.php
            $success_message = "Registration successful.";
            //header("Location: index.php");
            //exit; // Stop script execution after successful registration
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-auth.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="container">
        <h2>User Registration</h2>
        <div id="message" style="color:red;">
            <?php 
                if(isset($username_error)) echo $username_error; 
                elseif(isset($password_error)) echo $password_error;
            ?>
        </div>
        <?php 
        if (!empty($success_message)) {
            echo '<div style="color:green;">'.$success_message.'</div>';
        }
        ?>
        <form method="post" action="register.php">
            <input type="text" name="username"  placeholder="Username" required><br>
            <input type="text" name="firstname" placeholder="First Name" required><br>
            <input type="text" name="lastname" placeholder="Last Name" required><br>
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" id="password" name="password" placeholder="Password" required><br>
            <input type="password" id="repeat_password" name="repeat_password" placeholder="Repeat Password" required><br>
            <input type="checkbox" onclick="showPassword()"> Show Password<br><br>
            
            <select name="role">
                <option value="admin">Admin</option>
                <option value="checker">Checker</option>
                <option value="employee">Employee</option>
                <option value="student">Student</option>
            </select><br>
            <input type="submit" value="Register">
            <br><br>
            Or
            <br><br>
            <div id="googleSignIn">
                <button id="googleSignInButton" onclick="googleSignIn()">
                    <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google Logo" height="18">
                    Sign in with Google
                </button>
            </div>
        </form>
    </div>
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

                // Determine user role based on email
                var email = user.email;
                var role = 'employee'; // Default role
                if (email === 'admin@example.com') {
                    role = 'admin';
                } else if (email === 'checker@example.com') {
                    role = 'checker';
                }

                // Fill the registration form with Google user's info
                document.querySelector('input[name="username"]').value = user.displayName.replace(/\s+/g, '').toLowerCase();
                document.querySelector('input[name="firstname"]').value = user.displayName.split(' ')[0];
                document.querySelector('input[name="lastname"]').value = user.displayName.split(' ').slice(1).join(' ');
                document.querySelector('input[name="email"]').value = user.email;
                document.querySelector('select[name="role"]').value = role;
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

        function showPassword() {
            var passwordInput = document.getElementById("password");
            var repeatPasswordInput = document.getElementById("repeat_password");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                repeatPasswordInput.type = "text";
            } else {
                passwordInput.type = "password";
                repeatPasswordInput.type = "password";
            }
        }
    </script>
</body>
</html>

