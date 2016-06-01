<?php
require 'vendor/autoload.php';
include_once 'connect.php';

use \Slim\Slim;

define("CLIENT_ID", "716f19ff9da54d3287d49a149aa169e8");
define("CLIENT_SECRET", "f4daaf18a2ec4498abf2a2ff308bbebc");
define("REDIRECT_URL", "http://potential.reach/login");
$app = new \Slim\App();

// Get container
$container = $app->getContainer();

// Register component on container
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('templates', [
        'cache' => 'tmp'
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container['router'],
        $container['request']->getUri()
    ));
    return $view;
};

$app->get('/login', function( $req, $res, $args ) {
    $data = array();
    $login_url = '';
    if( isset( $_GET['code'] ) ){
        $code = $_GET['code'];
        $postarr =  array( 'client_id' => CLIENT_ID,
			               'client_secret' => CLIENT_SECRET,
			               'grant_type' => 'authorization_code',
			               'redirect_uri' => REDIRECT_URL,
			               'code' => $code );
        $url = 'https://api.instagram.com/oauth/access_token';
        $ch = curl_init();
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postarr);
		$response = curl_exec($ch);
		curl_close($ch);
        $data =json_decode($response, True);
        if( isset( $data['code'] ) && $data['code'] == 400 ){
        	print_r("Please Re-Try Again");exit;
        } else {
        	$token = savetoDb( $data );
        }
    } else{
        $login_url = "https://api.instagram.com/oauth/authorize?client_id=".CLIENT_ID."&redirect_uri=".REDIRECT_URL."&scope=basic&response_type=code";
        return $res->withStatus(302)->withHeader('Location', $login_url );
    }
});

function savetoDb( $data ){
	global $conn;
	$searchQuery = "SELECT * FROM ".DBNAME.".`users` WHERE `username` = '".$data['user']['username']."'";
	$result = mysqli_fetch_array( mysqli_query( $conn, $searchQuery ) ); 
	if( mysqli_num_rows( $result ) > 0 ){
	    return $result['access_token'];
	} else{
	    $query = "INSERT INTO ".DBNAME.".`users` (id, name, access_token, username, bio, website, profile_picture, insta_id ) 
			  VALUES ( 
			  	NULL, 
			  	'".$data['user']['full_name']."', 
			  	'".$data['access_token']."', 
			  	'".$data['user']['username']."',
		     	'".$data['user']['bio']."', 
		     	'".$data['user']['website']."', 
		     	'".$data['user']['profile_picture']."', 
		     	".$data['user']['id']."
	     	 );";
		$result = mysqli_query( $conn, $query );
		return $data['access_token'];
	}
}
$app->run();
?>