<!DOCTYPE html>
<html>

<head>
    <title>Safety Induction Certificate</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }

        .certificate {
            border: 2px solid #000;
            padding: 20px;
            margin: 20px;
        }

        h1 {
            color: #004085;
        }
    </style>
</head>

<body>
    <div class="certificate">
        <h1>Safety Induction Certificate</h1>
        <p> Awarded to: {{ $user->name }} </p>
        <p> For: {{ $induction->name }} </p>
        <p> Issued on: {{ $issued_date }} </p>
    </div>
</body>

</html>
