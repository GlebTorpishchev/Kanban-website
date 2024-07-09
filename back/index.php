<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form action="register.php" method="post">

        <input type="text" placeholder="Логин" name="login">
        <input type="text" placeholder="Пароль" name="pass">
        <input type="text" placeholder="Email" name="email">
        <button type="submit">Регистрация</button>
    </form>


    <form action='login.php' method="post">
        <input type="text" placeholder="Логин" name="login">
        <input type="text" placeholder="Пароль" name="pass">
        <button type="submit">Войти</button>
    </form>

</body>

</html>