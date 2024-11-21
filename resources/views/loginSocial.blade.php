<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Login Button</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }

        .google-button-container {
            text-align: center;
        }

        .google-button {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            background-color: white;
            border: 1px solid #dcdcdc;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            font-size: 16px;
            color: #555;
            cursor: pointer;
            transition: background-color 0.2s, box-shadow 0.2s;
        }

        .google-button img {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }

        .google-button:hover {
            background-color: #f7f7f7;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .google-button:active {
            background-color: #eaeaea;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body>
    <div class="google-button-container">
        <a class="google-button" href="{{route('google-auth')}}">
            <img src="https://th.bing.com/th/id/OIP.D6P-BO32wCApcPIIjt6p5wHaHa?w=175&h=180&c=7&r=0&o=5&dpr=1.3&pid=1.7" alt="Google Logo">
            Continue with Google
        </a>
    </div>
</body>

</html>
