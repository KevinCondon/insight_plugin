
<?php
/**
 * Plugin name: Query APIs
 * version: 1.0.0
 * text-domain: query-apis
 */

defined( 'ABSPATH' ) or die( 'Unauthorized Access' );


function call_api($query){

	$url = 'https://community-open-weather-map.p.rapidapi.com/weather';
	
	$args = array(
		'q' => $query,
		'lat' => '0',
		'lon' => '0',
		'lang' => 'null',
		'units' => '"metric" or "imperial"',
		'mode' => 'xml, html'

	);

	$method = 'GET';

	$headers = array(
		'x-rapidapi-key' => 'c58ec0ba3dmshe5bfd84be320dd9p1a8899jsnf981b8d912fb',
		'x-rapidapi-host' => 'community-open-weather-map.p.rapidapi.com');

	$request = array(
		'headers' => $headers,
		'method'  => $method,
	);
	
	$url = add_query_arg( $args, $url );



	$response = wp_remote_request( $url, $request );

	$body = json_decode( wp_remote_retrieve_body( $response ));

	if ($body->cod == 404 ) {
		echo  "Something went wrong: " . $body->message;
	} else {

		echo '<pre>';

		$body = json_decode( wp_remote_retrieve_body( $response ));

		echo ('<h1>'.($body->name).'</h1>');
echo ('<h2>'.convert_temp_kelvin_fah($body->main->temp) . ' Degrees'. '</h2>');

echo '<br><br>';
print_r($body);

echo '</pre>';
}
}

function call_api_google($query){

	$url = 'https://maps.googleapis.com/maps/api/place/findplacefromtext/json?input=' . 
	$query 
	. '&key=AIzaSyCGd5U4O8CHvwzK3qGkls2yXvHYgwgmKqg&inputtype=textquery&fields=name,photos';


	$response = wp_remote_get( $url);
		//var_dump( wp_remote_retrieve_body( $response ));

	$body = json_decode(wp_remote_retrieve_body( $response ));
	if($body->status == 'ZERO_RESULTS')
	{

		return;

	} else {

		$url = 'https://maps.googleapis.com/maps/api/place/photo?photoreference=' . 
		$body->candidates[0]->photos[0]->photo_reference . 
		'&key=AIzaSyCGd5U4O8CHvwzK3qGkls2yXvHYgwgmKqg&maxwidth=400&maxheight=400';

		$response = wp_remote_get( $url);
		$image = wp_remote_retrieve_body( $response );
		//$body = json_decode(wp_remote_retrieve_body( $response ));
		//echo imagejpeg($image);
		//imagejpeg($image);
		//imagecreatefromstring(base64_encode($image));
		//echo base64_encode($image);
		//header('Content-Type: image/png');
		//$image = base64_encode($image);

		// $im = imagecreatefromstring($image);
	  	// header('Content-Type: image/png');
			// imagejpeg($im);

		//FINALLY WORKS!!!

		$encoded_image=base64_encode($image);

		$mime='image/gif';

		$binary_data = 'data:' . $mime . ';base64,' . $encoded_image ;

		echo ( '<img src=' .$binary_data .' alt=”Test”>');


	}


}

function scripts_page(){
	
	if(array_key_exists('submit_call_api',$_POST))
	{
		call_api_google($_POST['city'] . ', ' . $_POST['country']);
		call_api($_POST['city'] . ',' . $_POST['country']);

	}

	?>
	
	<div class="wrap">
		<h2>Find the weather</h2>
		<form method="post" action="">
			City:<input type=text name="city" value="London">
			Country initals<input type=text name="country" value="UK">

			<input type="submit" name="submit_call_api" class="button button-primary" value="CALL API">
		</form>
	</div>	

	

	<?php

}

function convert_temp_kelvin_fah($temp){
	$kelvin = floatval($temp);
	return ($kelvin - 273.15) * 1.8000+ 32.00;

}

function my_custom_menu_page() {
	add_menu_page(
		__( 'Query API Test Settings', 'query-apis' ),
		'Query API Test',
		'manage_options',
		'api-test.php',
		'scripts_page',
		'dashicons-testimonial',
		16
	);
}

add_action( 'admin_menu', 'my_custom_menu_page' );