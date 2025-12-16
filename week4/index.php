<?php
$name = $email = $pwd = $pwd_repeat = "";
$nameErr = $emailErr = $pwdErr = $pwd_repeatErr = "";
$success = "";
$errors = [];

function test_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if(empty ($_POST["name"])) {
    	$nameErr = "Name is required";
    } else {
    	$name = test_input($_POST["name"]);
    }

    if (empty($_POST["email"])) {
    	$emailErr = "Email is required";
    } else {
    	$email = test_input($_POST["email"]);
    	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    		$emailErr = "Invalid email format";
    	}
    }

    if (empty($_POST["pwd"])) {
    	$pwdErr = "Password is required";
    } else {
    	$pwd = test_input($_POST["pwd"]);

        if (strlen($pwd) < 6) {
            $pwdErr = "Password must be at least 6 characters";
        } elseif (!preg_match("/[@#$%&*!]/", $pwd)) {
            $pwdErr = "Password must include a special character (@ # $ % & * !)";
        }
    }

    if (empty($_POST["pwd_repeat"])) {
    	$pwd_repeatErr = "Repeat the Password";
    } else {
    	$pwd_repeat = test_input($_POST["pwd_repeat"]);
    	if ($pwd !== $pwd_repeat) {
            $pwd_repeatErr = "Passwords do not match";
        }
    }

    if (empty($nameErr) && empty($emailErr) && empty($pwdErr) && empty($pwd_repeatErr)) {

        $file = "users.json";
        if (!file_exists($file)) {
            if (file_put_contents($file, json_encode([])) === false) {
                $errors[] = "Error creating users file. Please check file permissions.";
            }
        }
        $currentData = file_get_contents($file);
        if ($currentData === false) {
            $errors[] = "Error reading users file. Please try again later.";
        } else {
            $users = json_decode($currentData, true);
            if (!is_array($users)) $users = [];
        }
        $emailExists = false;
        foreach ($users as $u) {
            if (strtolower($u['email']) === strtolower($email)) {
                $emailErr = "Email already registered";
                $emailExists = true;
                break;
            }
        }

        if (!$emailExists && empty($errors)) {
            $user = [
                "name" => $name,
                "email" => $email,
                "password" => password_hash($pwd, PASSWORD_DEFAULT)
            ];

            $users[] = $user;

            if (file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT)) === false) {
                $errors[] = "Error saving user data. Please check file permissions.";
            } else {
                $success = "Registration successful!";
                // Clear form values after success
                $name = $email = $pwd = $pwd_repeat = "";
            }
        }
    }
}
?>

<?php if (!empty($errors)) : ?>
    <div style="color: red; border: 1px solid red; padding: 10px;">
        <?php foreach ($errors as $err) {
            echo "<p>$err</p>";
        } ?>
    </div>
<?php endif; ?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Registration System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php if (!empty($success)) : ?>
    <div style="color: green; border: 1px solid green; padding: 10px;">
        <?= $success ?>
    </div>
<?php endif; ?>


    <form method="post">
    <h1>User Registration</h1>
    <p>Please fill in this form to create an account.</p>
    <hr>

        <label for="Name">Name:</label>
        <input type="text" name="name"  value="<?= $name ?>">
        <span style="color:red"> <?= $nameErr ?></span><br><br>
        <br><br>

        <label for="Email">Email:</label>
        <input type="email" name="email"  value="<?= $email ?>">
        <span style="color:red"> <?= $emailErr ?></span><br><br>
        <br><br>

        <label for="Password">Password:</label>
        <input type="password" name="pwd">
        <span style="color:red"> <?= $pwdErr ?></span><br><br>
        <br><br>

        <label for="Password-Repeat">Repeat Password:</label>
        <input type="password" name="pwd_repeat">
        <span style="color:red"> <?= $pwd_repeatErr ?></span><br><br>
        <br><br>

        <p> By creating an account you agree to our <a href="#">Terms and Privacy</a></p>
        <button type="submit" class="registerbtn"> Register </button>
    <div class="container signin">
        <p>Already have an account? <a href="#">Sign in.</a></p>
    </div>
</form>
</body>
</html>