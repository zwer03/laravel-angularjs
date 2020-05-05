<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Online PF - Providence Hospital Inc.</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="icon" href="http://providencehospital.com.ph/wp-content/uploads/2018/02/cropped-cropped-PHI-ICON-32x32.png" sizes="32x32">
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    Providence Hospital
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            {{-- @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif --}}
                        @else
                            @role('Admin')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('users.index') }}"><i class="fa fa-users"></i>{{ __('Users') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('configurations.index') }}"><i class="fa fa-cog"></i>{{ __('Configurations') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('consultant_types.index') }}"><i class="fas fa-user-md"></i>{{ __('Consultant Types') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('sms_templates.index') }}"><i class="fa fa-sms"></i>{{ __('SMS Templates') }}</a>
                            </li>
                           <!--  <li class="nav-item">
                                <a class="nav-link" href="{{ route('audit_logs.index') }}"><i class="fas fa-book"></i>{{ __('Audit Logs') }}</a>
                            </li> -->
                            @endrole
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('physician.dashboard') }}"><i class="fa fa-procedures"></i>{{ __('My Patients') }}</a>
                            </li>
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="fa fa-book"></i>Reports <span class="caret"></span>
                                </a>
                                
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('reports.pf_summary') }}"><i class="fas fa-book"></i>{{ __('PF Summary') }}</a>
                                    @role('Admin')
                                    <a class="dropdown-item" href="{{ route('audit_logs.index') }}"><i class="fas fa-book"></i>{{ __('Audit Logs') }}</a>
                                    @endrole
                                </div>
                               
                            </li>
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <?php $default_pf_type = App\Http\Controllers\PhysicianController::get_config('default_pf_type') ?>
                                    @if($default_pf_type->value)
                                    <a class="dropdown-item" href="#" id="default-pf-type">
                                        <i class="fas fa-money-check"></i>{{ __('Default PF Type') }}
                                    </a>
                                    @endif
                                    <a class="dropdown-item" href="#" id="change-password">
                                        <i class="fa fa-edit"></i>{{ __('Change Password') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt"></i>{{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container py-3">
            <div class="row">
                <div class="col">              
                    @include ('errors.list') {{-- Including error file --}}
                </div>
            </div>
            @if (session('status'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {{ session('status') }}
                </div>
            @endif
        </div>
        <main class="">
            @yield('content')
        </main>
    </div>
    @include('shared.change_password')
    @include('shared.default_pf_type')
</body>
</html>
