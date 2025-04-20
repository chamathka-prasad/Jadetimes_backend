<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Register</title>
</head>
<body>
    
    <h3>User Registration Form</h3>

    <form action="includes/registration.inc.php" method="post">
        <input type="text" name="username" placeholder="Username">
        <input type="email" name="email" placeholder="Email">
        <input type="password" name="pwd" placeholder="Password">
        <input type="text" name="role" placeholder="Role">
        <button>Register</button>
    </form>
</body>
</html>