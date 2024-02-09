<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Not found!</title>
  <link rel="stylesheet" href="resources/css/style.css">
</head>

<body>
  <?php include 'layout/header.php'; ?>
  <div style="
  width: 100vw; 
  height: 100vh; 
  padding: 100px 0px 125px 0px; 
  display: flex;
  align-items: center; 
  justify-content: center; 
  flex-direction: column;">
    <h1>Puslapis nerastas!</h1>
    <a style="
    text-decoration: none; 
    color: #3bd461;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);" href="/">Grįžti į pagrindinį puslapį</a>
    <img class="error-404-image" style="
    margin-left: 10px;
    width: 95vw;
    max-width: 600px;
    height: auto;
    object-fit: contain;" src="../resources/img/error-404.svg" alt="">
    <?php include 'layout/footer.php'; ?>
  </div>
</body>

</html>