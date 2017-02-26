<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('app.name', 'Laravel') }} | S'enregistrer</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.6 -->
    <link rel="stylesheet" href="{{ asset('adminLTE/bootstrap/css/bootstrap.min.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('adminLTE/dist/css/AdminLTE.min.css') }}">
    <!-- iCheck -->
    <link rel="stylesheet" href="{{ asset('adminLTE/plugins/iCheck/square/blue.css') }}">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="hold-transition register-page">
<div class="register-box">
    <div class="register-logo">
        <a href="#">{{ config('app.name', 'Laravel') }}</a>
    </div>
    <!-- /.login-logo -->
    <div class="register-box-body">
        <p class="login-box-msg">Création de compte pour l'administration</p>
        <form action="{{ route('register') }}" method="post">
            {{ csrf_field() }}
            <div class="form-group has-feedback {{ $errors->has('name') ? 'has-error' : '' }}">
                <input type="text" class="form-control" id="name" name="name" placeholder="Nom" required autofocus value="{{ old('name') }}">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                @if ($errors->has('name'))
                    <span class="help-block">
                        <strong>{{ $errors->first('name') }}</strong>
                    </span>
                @endif
            </div>
            <div class="form-group has-feedback {{ $errors->has('email') ? 'has-error' : '' }}">
                <input type="email" class="form-control" id="email" name="email" placeholder="Email" required value="{{ old('email') }}">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                @if ($errors->has('email'))
                    <span class="help-block">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                @endif
            </div>
            <div class="form-group has-feedback {{ $errors->has('password') ? 'has-error' : '' }}">
                <input type="password" class="form-control" placeholder="Mot de passe" id="password" name="password" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                @if ($errors->has('password'))
                    <span class="help-block">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                @endif
            </div>
            <div class="form-group has-feedback {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                <input type="password" class="form-control" placeholder="Mot de passe" id="password-confirm" name="password_confirmation" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row">
                <div class="col-xs-5">
                    <button type="submit" class="btn btn-primary btn-block btn-flat">S'enregistrer</button>
                </div>
                <!-- /.col -->
            </div>
        </form>
        <a href="{{ route('password.request') }}">J'ai perdu mon mot de passe</a><br>
    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->
<!-- jQuery 2.2.3 -->
<script src="{{ asset('adminLTE/plugins/jQuery/jquery-2.2.3.min.js') }}"></script>
<!-- Bootstrap 3.3.6 -->
<script src="{{ asset('adminLTE/bootstrap/js/bootstrap.min.js') }}"></script>
<!-- iCheck -->
<script src="{{ asset('adminLTE/plugins/iCheck/icheck.min.js') }}"></script>
<script>
    $(function () {
        $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });
    });
</script>
</body>
</html>