<!DOCTYPE html>
<html lang="en" class="no-js">
	<head>
		<title>Online PF - Providence Hospital Inc.</title>

		<meta charset="utf-8" />

		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
		<meta content="" name="description" />
		<meta content="" name="author" />

		<link href="{{ asset('css/app.css') }}" rel="stylesheet">
		<link rel="stylesheet" href="/assets/plugins/font-awesome/css/font-awesome.min.css">
		<link rel="stylesheet" href="/assets/plugins/animate.css/animate.min.css">
		<link rel="stylesheet" href="/assets/plugins/iCheck/skins/all.css">
		<link rel="stylesheet" href="/assets/css/styles.css">
		<link rel="stylesheet" href="/assets/css/styles-responsive.css">
		<link rel="stylesheet" href="/assets/plugins/iCheck/skins/all.css">
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
	</head>
	
	<body class="login">
        <div class="container">
            <div class="row d-flex justify-content-center">
                <div class="main-login col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-4 col-md-offset-4">
                    
                    <!-- start: LOGIN BOX -->
                    <div class="box-login" style="">
                        <!-- <div class="logo">
                            <img src="/img/providence/logonew.png" style="width: 200px;">
                            <br>
                        </div> -->
                        <h3>Sign in to your account</h3>
                        <p >
                            Please enter your username and password to log in.
                        </p>
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="inputGroupPrepend"><i class="fa fa-user"></i></span>
                                    </div>
                                    <input id="username" type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" placeholder="PRC Lic. No." required autocomplete="username" autofocus>
                                    @error('username')
                                        <div  class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="inputGroupPrepend"><i class="fa fa-lock"></i></span>
                                    </div>
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" value="{{ old('password') }}" placeholder="Password" required autocomplete="password" autofocus>
                                    @error('password')
                                        <div  class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    
                                </div>
                            </div>
                            @if(env('GOOGLE_RECAPTCHA_KEY'))
                                <div class="d-flex justify-content-center my-3">
                                    <div class="g-recaptcha" data-sitekey="{{env('GOOGLE_RECAPTCHA_KEY')}}" data-callback="callback" ></div>
                                </div>
                                @error('g-recaptcha-response')
                                    <div  class="d-flex justify-content-center text-danger">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            @endif
                            <div class="form-group">
                                <div class="col">
                                    <button type="submit" id="login-submit" class="btn btn-primary btn-block" disabled>
                                        {{ __('Login') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <!-- start: COPYRIGHT -->
                        <div class="copyright">
                            <span>2019 &copy; <a href="http://www.providencehospital.com.ph" target="_blank">Providence Hospital Inc.</a></span>
                        </div>
                        <!-- end: COPYRIGHT -->
                    </div>
                    <!-- end: LOGIN BOX -->
                </div>
            </div>
        </div>
		<!-- start: MAIN JAVASCRIPTS -->
		<!--[if lt IE 9]>
		<script src="assets/plugins/respond.min.js"></script>
		<script src="assets/plugins/excanvas.min.js"></script>
		<script type="text/javascript" src="assets/plugins/jQuery/jquery-1.11.1.min.js"></script>
		<![endif]-->
		<!--[if gte IE 9]><!-->
		<script src="/assets/plugins/jQuery/jquery-2.1.1.min.js"></script>
		<!--<![endif]-->
		<script src="/assets/plugins/jquery-ui/jquery-ui-1.10.2.custom.min.js"></script>
		<script src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
		<script src="/assets/plugins/iCheck/jquery.icheck.min.js"></script>
		<script src="/assets/plugins/jquery.transit/jquery.transit.js"></script>
		<script src="/assets/plugins/TouchSwipe/jquery.touchSwipe.min.js"></script>
		<script src="/assets/js/main.js"></script>
		<!-- end: MAIN JAVASCRIPTS -->
		<!-- start: JAVASCRIPTS REQUIRED FOR THIS PAGE ONLY -->
		<script src="/assets/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
		<script src="/assets/js/login.js"></script>
		<!-- end: JAVASCRIPTS REQUIRED FOR THIS PAGE ONLY -->
		<script>
            function callback(){
                document.getElementById('login-submit').disabled = false;
            }
			jQuery(document).ready(function() {
				Main.init();
				Login.init();
			});
		</script>
	</body>
	<!-- end: BODY -->
</html>