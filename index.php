<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
include_once 'connect.php';

use \Slim\Slim;

define("CLIENT_ID", "716f19ff9da54d3287d49a149aa169e8");
define("CLIENT_SECRET", "f4daaf18a2ec4498abf2a2ff308bbebc");
define("REDIRECT_URL", "http://localhost/login.html");
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
    $login_url = "https://api.instagram.com/oauth/authorize?client_id=".CLIENT_ID."&redirect_uri=".REDIRECT_URL."&scope=basic&response_type=token";
    return $res->withStatus(302)->withHeader('Location', $login_url );
});

$app->post('/session', function( $req, $res, $args ){
    $response = array( 'result'=> false, 'reason' => 'Invalid Authorisation' );
    $parsedBody = $req->getParsedBody();
    $accessToken = savetoDb( $parsedBody );
    if( $accessToken != null ){
        $response = array( 'result'=> true, 'reason' => 'success' );
    } 
    echo json_encode( $response );
});

function savetoDb( $data ){
	global $conn;
	$searchQuery = "SELECT * FROM ".DBNAME.".`users` WHERE `username` = '".$data['username']."'";
	$result = mysqli_fetch_array( mysqli_query( $conn, $searchQuery ) ); 
	if( $result != null && mysqli_num_rows( $result ) > 0 ){
	     return $result['access_token'];
	} else {
	    $query = "INSERT INTO ".DBNAME.".`users` (id, name, access_token, username, bio, website, profile_picture, insta_id ) 
			  VALUES ( 
			  	NULL, 
			  	'".$data['full_name']."', 
			  	'".$data['access_token']."', 
			  	'".$data['username']."',
		     	'".$data['bio']."', 
		     	'".$data['website']."', 
		     	'".$data['profile_picture']."', 
		     	".$data['userid']."
	     	 );";
		$result = mysqli_query( $conn, $query );
		return $data['access_token'];
	}
    return null;
}
$app->run();
?>