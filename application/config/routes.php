<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_methodreset_password
*/
$route['default_controller'] = 'auth';
$route['404_override'] = 'home';
$route['translate_uri_dashes'] = FALSE;
$route['register']['GET'] = 'auth/register';
$route['dashboard']['GET'] = 'home/index';
$route['history']['GET'] = 'home/history';
$route['profile']['GET'] = 'home/profile';
$route['game']['GET'] = 'home/game';
$route['change-password']['GET'] = 'home/change_password';
$route['play-game']['GET'] = 'home/opengame';
$route['play/(:any)']['GET'] = 'home/playOnce/$1';
$route['play/(:any)/(:any)']['GET'] = 'home/play/$1/$2';
$route['ref']['GET'] = 'home/ref';
$route['lobby/(:any)']['GET'] = 'home/lobby/$1';
$route['promotions']['GET'] = 'home/promotion';
$route['news']['GET'] = 'home/news';
$route['play_wheel']['GET'] = 'home/play_wheel';
