<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| AUTO-LOADER
| -------------------------------------------------------------------
| This file specifies which systems should be loaded by default.
|
| In order to keep the framework as light-weight as possible only the
| absolute minimal resources are loaded by default. For example,
| the database is not connected to automatically since no assumption
| is made regarding whether you intend to use it.  This file lets
| you globally define which systems you would like loaded with every
| request.
|
| -------------------------------------------------------------------
| Instructions
| -------------------------------------------------------------------
|
| These are the things you can load automatically:
|
| 1. Packages
| 2. Libraries
| 3. Drivers
| 4. Helper files
| 5. Custom config files
| 6. Language files
| 7. Models
|
*/

/*
| -------------------------------------------------------------------
|  Auto-load Packages
| -------------------------------------------------------------------
| Prototype:
|
|  $autoload['packages'] = array(APPPATH.'third_party', '/usr/local/shared');
|
*/
$autoload['packages'] = array();

/*
| -------------------------------------------------------------------
|  Auto-load Libraries
| -------------------------------------------------------------------
| These are the classes located in system/libraries/ or your
| application/libraries/ directory, with the addition of the
| 'database' library, which is somewhat of a special case.
|
| Prototype:
|
|	$autoload['libraries'] = array('database', 'email', 'session');
|
| You can also supply an alternative library name to be assigned
| in the controller:
|
|	$autoload['libraries'] = array('user_agent' => 'ua');
*/
$autoload['libraries'] = array(
                                'upload',
                                'image_lib',
                                'session',
                                'database',
								'sms_librarie',
                                'game_api_librarie',
                                'linelogin_librarie',
                              );

/*
| -------------------------------------------------------------------
|  Auto-load Drivers
| -------------------------------------------------------------------
| These classes are located in system/libraries/ or in your
| application/libraries/ directory, but are also placed inside their
| own subdirectory and they extend the CI_Driver_Library class. They
| offer multiple interchangeable driver options.
|
| Prototype:
|
|	$autoload['drivers'] = array('cache');
|
| You can also supply an alternative property name to be assigned in
| the controller:
|
|	$autoload['drivers'] = array('cache' => 'cch');
|
*/
$autoload['drivers'] = array('cache');

/*
| -------------------------------------------------------------------
|  Auto-load Helper Files
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['helper'] = array('url', 'file');
*/
$autoload['helper'] = array(
                             'url',
                             'form',
                             'text',
                             'string',
                             'cookie',
                             'array',
                             'function',
                             'directory',
                             'file',
                             'data',
                             'project'
                           );

/*
| -------------------------------------------------------------------
|  Auto-load Config files
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['config'] = array('config1', 'config2');
|
| NOTE: This item is intended for use ONLY if you have created custom
| config files.  Otherwise, leave it blank.
|
*/
$autoload['config'] = array();

/*
| -------------------------------------------------------------------
|  Auto-load Language files
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['language'] = array('lang1', 'lang2');
|
| NOTE: Do not include the "_lang" part of your file.  For example
| "codeigniter_lang.php" would be referenced as array('codeigniter');
|
*/
$autoload['language'] = array();

/*
| -------------------------------------------------------------------
|  Auto-load Models
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['model'] = array('first_model', 'second_model');
|
| You can also supply an alternative model name to be assigned
| in the controller:
|
|	$autoload['model'] = array('first_model' => 'first');
*/
$autoload['model'] = array(
  'Member_model',
  'Account_model',
  'Finance_model',
  'Use_promotion_model',
  'Bank_model',
  'Withdraw_model',
  'Promotion_model',
  'Setting_model',
  'Ref_model',
  'Wallet_deposit_model',
  'Wallet_ref_deposit_model',
  'Agent_commission_model',
  'Log_sms_model',
  'New_model',
  'Log_deposit_withdraw_model',
  'Transaction_model',
  'Log_line_notify_model',
  'Feature_status_model',
  'Log_wheel_model',
  'Log_add_credit_model',
  'Log_return_balance_model',
  'Bank_register_ignore_model',
  'Report_business_benefit_model',
	'Group_menu_model',
	'Menu_model',
	'Node_menu_model',
	'Permission_menu_role_model',
	'Permission_role_model',
	'Role_model',
);

$autoload['time_zone'] = date_default_timezone_set('Asia/Bangkok');
