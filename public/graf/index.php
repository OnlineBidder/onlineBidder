<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />

        <title>Pretty Charts with jQuery and AJAX | Tutorialzine Demo</title>
		<link href="assets/css/xcharts.min.css" rel="stylesheet">
		<link href="assets/css/style.css" rel="stylesheet">

		<!-- Include bootstrap css -->
		<link href="assets/css/daterangepicker.css" rel="stylesheet">
		<link href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.2.2/css/bootstrap.min.css" rel="stylesheet" />

    </head>
    <body>
		<div id="content">
			
			<form class="form-horizontal">
			  <fieldset>
		        <div class="input-prepend">
		          <span class="add-on"><i class="icon-calendar"></i></span><input type="text" name="range" id="range" />
		        </div>
			  </fieldset>
			</form>
			
			<div id="placeholder">
				<figure id="chart"></figure>
			</div>

		</div>

		<footer>
            <h2><i>Tutorial:</i> Pretty Charts with jQuery and AJAX</h2>
            <a class="tzine" href="http://tutorialzine.com/2013/01/charts-jquery-ajax/">Head on to <i>Tutorial<b>zine</b></i> to read and download</a>
        </footer>

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>

		<!-- xcharts includes -->
		<script src="//cdnjs.cloudflare.com/ajax/libs/d3/2.10.0/d3.v2.js"></script>
		<script src="assets/js/xcharts.min.js"></script>

		<!-- The daterange picker bootstrap plugin -->
		<script src="assets/js/sugar.min.js"></script>
		<script src="assets/js/daterangepicker.js"></script>

		<!-- Our main script file -->
		<script src="assets/js/script.js"></script>

		<!-- BSA AdPacks code. Please ignore and remove.-->
        <script src="http://cdn.tutorialzine.com/misc/adPacks/v1.js" async></script>
    </body>
</html>

