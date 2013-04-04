<?php
	// live site = http://thingiverse.com/login/oauth/access_token
	$auth_url = 'http://thingiverse.dev:8888/login/oauth/access_token';
	// set client id and secret to your own app!
	$client_id = 'ec403c97e7468a2bf3e0';
	$client_secret = 'e743edeb6a185ec05f306d9eb63403ad';
	$code = $_GET['code'];
	
	$context = stream_context_create(array(
		'http' => array(
			'method' => 'POST',
			'header' => 'Content-Type: application/x-www-form-urlencoded',
			'content' => http_build_query(array(
				'client_id' => $client_id,
				'client_secret' => $client_secret,
				'code' => $code
			))
		)
	));
	
	// quick error checking...
	// file_get_contents raises a warning instead of throwing an exception :(
	set_error_handler("warning_handler", E_WARNING);
	$result = file_get_contents($auth_url, false, $context);
	restore_error_handler();
	
	parse_str($result, $result_array);
	$access_token = $result_array['access_token'];
	
	function warning_handler($errno, $errstr) {
		print "Error $errno: $errstr";
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>three.js - thingiverse loader</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
		<style>
			body {
				font-family: Monospace;
				background-color: #000;
				color: #fff;
				margin: 0px;
				overflow: hidden;
			}
			#info {
				color: #fff;
				position: absolute;
				top: 10px;
				width: 100%;
				text-align: center;
				z-index: 100;
				display:block;
			}
			#info a, .button { color: #f00; font-weight: bold; text-decoration: underline; cursor: pointer }
		</style>
	</head>

	<body>
		<div id="info">
			<a href="http://threejs.org" target="_blank">three.js</a> - <a href="http://thingiverse.com">thingiverse</a> loader<br/>
			<input type="button" value="Find Thing" onclick="TV.dialog('thing_search', {q: 'makerbot'}, selectedThing)"/>
		</div>

		<script src="js/json2.js"></script>
		<script src="js/jquery-1.8.2.min.js"></script>
		<script src="js/jquery.ba-postmessage.min.js"></script>
		<script src="js/tviframesdk.js"></script>
		<script src="js/app.js"></script>
		<script src="js/three.min.js"></script>
		<script src="js/TrackballControls.js"></script>
		<script src="js/Detector.js"></script>
		<script src="js/stats.min.js"></script>

		<script>

			if ( ! Detector.webgl ) Detector.addGetWebGLMessage();

			var container, stats;

			var camera, controls, scene, renderer, loader, material, loadCallback;

			var cross;

			init();
			animate();

			function init() {

				camera = new THREE.PerspectiveCamera( 60, window.innerWidth / window.innerHeight, 0.01, 1e10 );
				camera.position.z = 100;

				controls = new THREE.TrackballControls( camera );

				controls.rotateSpeed = 5.0;
				controls.zoomSpeed = 5;
				controls.panSpeed = 2;

				controls.noZoom = false;
				controls.noPan = false;

				controls.staticMoving = true;
				controls.dynamicDampingFactor = 0.3;

				scene = new THREE.Scene();

				scene.add( camera );

				// light

				var dirLight = new THREE.DirectionalLight( 0xffffff );
				dirLight.position.set( 200, 200, 1000 ).normalize();

				camera.add( dirLight );
				camera.add( dirLight.target );

				material = new THREE.MeshLambertMaterial( { color:0xffffff, side: THREE.DoubleSide } );

				loader = new THREE.JSONLoader();
				loadCallback = function ( geometry, materials ) {
					var obj, i;
					for ( i = scene.children.length - 1; i >= 0 ; i -- ) {
					    obj = scene.children[ i ];
					    if ( obj !== plane && obj !== camera) {
					        scene.remove(obj);
					    }
					}
					var mesh = new THREE.Mesh( geometry, material );
					scene.add( mesh );
				};
        		// var url = "http://www.thingiverse.com/download:167199?format=json";
        		var url = "http://thingiverse-production.s3.amazonaws.com/threejs_json/77/c8/5b/5e/82/67d78769DunnyFigure4in.js";
				loader.load(url, loadCallback);

				// renderer

				renderer = new THREE.WebGLRenderer( { antialias: false } );
				renderer.setClearColorHex( 0x000000, 1 );
				renderer.setSize( window.innerWidth, window.innerHeight );

				container = document.createElement( 'div' );
				document.body.appendChild( container );
				container.appendChild( renderer.domElement );

				stats = new Stats();
				stats.domElement.style.position = 'absolute';
				stats.domElement.style.top = '0px';
				container.appendChild( stats.domElement );

				//

				window.addEventListener( 'resize', onWindowResize, false );

			}

			function onWindowResize() {

				camera.aspect = window.innerWidth / window.innerHeight;
				camera.updateProjectionMatrix();

				renderer.setSize( window.innerWidth, window.innerHeight );

				controls.handleResize();

			}

			function animate() {

				requestAnimationFrame( animate );

				controls.update();
				renderer.render( scene, camera );

				stats.update();

			}

		</script>

		<script>
			// api_url, target_url, and target are optional - set here for dev environment
			TV.init({
				access_token: '<?= $access_token ?>',
				api_url: 'http://api.thingiverse.dev:8888',
				target_url: 'http://thingiverse.dev:8888',
				target: parent
			});
			
			TV.api('/users/me', gotUser);
		</script>
	</body>
</html>
