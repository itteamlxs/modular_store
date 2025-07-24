<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($subject) ?></title>
</head>
<body>
    <h1><?= htmlspecialchars($subject) ?></h1>
    <p><?= nl2br(htmlspecialchars($body)) ?></p>
</body>
</html>