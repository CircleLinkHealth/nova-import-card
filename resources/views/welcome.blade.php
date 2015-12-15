<html>
	<head>
		<title>CircleHealth CPM API</title>

		<link href='//fonts.googleapis.com/css?family=Lato:100' rel='stylesheet' type='text/css'>
		<link href="{{ asset('/css/app.css') }}" rel="stylesheet">

        <script src="//code.jquery.com/jquery-1.10.2.js"></script>


        <style>
			body {
				margin: 0;
				padding: 0;
				width: 100%;
				height: 100%;
				color: #B0BEC5;
				display: table;
				font-weight: 100;
				font-family: 'Lato';
			}

			.container-fluid {
				text-align: center;
				display: table-cell;
				vertical-align: middle;
			}

			.content {
				text-align: center;
				display: inline-block;
			}

			.title {
				font-size: 96px;
				margin-bottom: 40px;
			}

			.subtitle {
				font-size: 24px !important;
			}
		</style>
	</head>
	<body>
		<div class="container-fluid">
			<div class="content">
				<div class="title">CircleLink Health<br>Care Plan Manager</div>
				<a class="btn btn-primary subtitle" href="{{ url('/auth/login') }}">Login</a>
			</div>
		</div>
    </body>
</html>
