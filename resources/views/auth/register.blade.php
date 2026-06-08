<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Runa Realm Register</title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:Arial, Helvetica, sans-serif;
        }

        body{
            background:#050505;
            color:white;
            overflow:hidden;
        }

        .bg{
            position:absolute;
            width:100%;
            height:100%;
            background:url('https://images6.alphacoders.com/135/1353380.png');
            background-size:cover;
            background-position:center;
            filter:brightness(20%);
        }

        .overlay{
            position:absolute;
            width:100%;
            height:100%;
            background:linear-gradient(
            to right,
            rgba(0,0,0,0.9),
            rgba(0,0,0,0.5)
            );
        }

        .container{
            position:relative;
            z-index:2;
            width:100%;
            height:100vh;
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:80px;
        }

        .left{
            width:55%;
        }

        .small{
            color:#7b7bff;
            letter-spacing:4px;
            font-size:12px;
            margin-bottom:20px;
        }

        .title{
            font-size:110px;
            line-height:0.9;
            margin-bottom:30px;
        }

        .title span{
            color:#7b7bff;
            font-style:italic;
        }

        .desc{
            width:70%;
            color:#ccc;
            line-height:1.8;
            font-size:18px;
        }

        .login-box{
            width:400px;
            padding:40px;
            border-radius:25px;
            background:rgba(255,255,255,0.05);
            border:1px solid rgba(255,255,255,0.1);
            backdrop-filter:blur(15px);
            box-shadow:0 0 40px rgba(0,0,0,0.5);
        }

        .login-box h2{
            font-size:45px;
            margin-bottom:10px;
        }

        .login-box p{
            color:#aaa;
            margin-bottom:30px;
            line-height:1.6;
        }

        .input-box{
            margin-bottom:20px;
        }

        .input-box input{
            width:100%;
            padding:16px;
            border:none;
            border-radius:14px;
            background:#0d0d0d;
            border:1px solid #222;
            color:white;
            outline:none;
            transition:0.3s;
        }

        .input-box input:focus{
            border:1px solid #7b7bff;
            box-shadow:0 0 10px #7b7bff55;
        }

        .btns{
            display:flex;
            gap:15px;
            margin-top:20px;
        }

        .btns button{
            width:100%;
            padding:15px;
            border:none;
            border-radius:14px;
            cursor:pointer;
            font-size:15px;
            transition:0.3s;
        }

        .signin{
            background:#6c63ff;
            color:white;
        }

        .signin:hover{
            background:#857dff;
            transform:translateY(-2px);
        }

        .register{
            background:#111;
            color:white;
            border:1px solid #333 !important;
        }

        .register:hover{
            background:#1a1a1a;
        }

        a{
            width:100%;
        }

        .top-logo{
            position:absolute;
            top:30px;
            left:40px;
            z-index:10;
            font-size:28px;
            font-weight:bold;
            color:#7b7bff;
        }

    </style>

</head>

<body>

<div class="bg"></div>
<div class="overlay"></div>

<div class="top-logo">
    🌙 RUNA
</div>

<div class="container">

    <div class="left">

        <p class="small">
            JOIN US / CREATE YOUR REALM
        </p>

        <h1 class="title">
            Join <br>
            <span>Runa.</span>
        </h1>

        <p class="desc">
            Create an account to save your favorite manga, track your progress, and join the community.
        </p>

    </div>

    <div class="login-box">

        <p class="small">
            REGISTER / NEW GUEST
        </p>

        <h2>
            Sign up
        </h2>

        <p>
            Create your account to start your adventure.
        </p>

        @if ($errors->any())
            <div style="background: rgba(255, 0, 0, 0.2); color: #ff6b6b; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid rgba(255, 0, 0, 0.3);">
                <ul style="list-style: none; padding: 0; margin: 0; font-size: 14px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">

            @csrf

            <div class="input-box">

                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Enter your name"
                    required
                >

            </div>

            <div class="input-box">

                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Enter your email"
                    required
                >

            </div>

            <div class="input-box">

                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >

            </div>

            <div class="input-box">

                <input
                    type="password"
                    name="password_confirmation"
                    placeholder="Confirm your password"
                    required
                >

            </div>

            <div class="btns">

                <button class="signin" type="submit">
                    REGISTER
                </button>

                <a href="/login">

                    <button class="register" type="button">
                        SIGN IN
                    </button>

                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>
