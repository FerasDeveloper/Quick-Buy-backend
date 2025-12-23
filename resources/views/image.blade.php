<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
</head>
<body>
  <input type="file" />


  <img src='{{ asset("storage/" . $product->image) }}' alt="no" style="max-width: 300px; max-height: 300px" />
</body>
</html>