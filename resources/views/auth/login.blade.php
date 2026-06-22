<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Smart Water Discharge Monitoring</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #dff5e7, #e5f3ff);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .auth-card {
            width: 420px;
            background: white;
            border: 2px solid #1f1f1f;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }

        h1 {
            text-align: center;
            font-size: 22px;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            color: #555;
            margin-bottom: 25px;
            font-size: 14px;
        }

        label {
            font-weight: bold;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 8px 0 16px;
            border: 1.5px solid #333;
            border-radius: 10px;
            font-size: 14px;
        }

        button, .guest-btn, .register-link {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: block;
            text-align: center;
            margin-top: 10px;
        }

        button {
            background: #67c46a;
            color: white;
        }

        .guest-btn {
            background: #5dade2;
            color: white;
        }

        .register-link {
            background: #f3f3f3;
            color: #222;
            border: 1.5px solid #222;
        }

        .message {
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>

    <div class="auth-card">
        <h1>Smart Water Discharge Monitoring</h1>
        <p class="subtitle">Login to view the monitoring dashboard</p>

        @if(session('success'))
            <div class="message success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="message error">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST">
            @csrf

            <label>Email</label>
            <input type="email" name="email" placeholder="Enter your email" value="{{ old('email') }}" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Enter your password" required>

            <button type="submit">Login</button>
        </form>

        <a href="{{ route('guest.login') }}" class="guest-btn">Login as Guest</a>
        <a href="{{ route('register') }}" class="register-link">Create an Account</a>
    </div>

</body>
</html>