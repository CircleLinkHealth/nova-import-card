<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>CPM API</title>

	<link href="{{ mix('/css/app.css') }}" rel="stylesheet">
	<link href="{{ mix('/img/favicon.png') }}" rel="icon">

	<!-- Fonts -->
	<link href='//fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>

	<!-- Scripts -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.min.css" integrity="sha256-iWTx/iC9IoKaoSKD5+WVFef8ZYNIgQ4AxVpMbBw2hig=" crossorigin="anonymous" />
	<script src="//code.jquery.com/jquery-1.10.2.js"></script>
	<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

	<script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/js/bootstrap.min.js"></script>
</head>
<body>
	<nav class="navbar navbar-default">
		<div class="container-fluid">
			<div class="navbar-header">

				<a class="navbar-brand" href="">
					<img src="{{mix('/img/cpm-logo.png')}}" height="40" width="70">
				</a>
			</div>

			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<h1 class="text-center"></h1>
			</div>
		</div>
	</nav>
	<!--[if lt IE 8]>
		<p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
		<![endif]-->

	@yield('content')
	<div style="clear:both;height:100px;"></div>
</body>
</html>
