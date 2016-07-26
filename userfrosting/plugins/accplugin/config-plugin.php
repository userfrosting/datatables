<?php

/*
  Plugin Name:  CapableData - DataTables Plugin v 0.1
  Description:  CapableData - DataTables Plugin v 0.1
  Author: Srinivas Nukala
  Version: 0.1
 */

namespace UserFrosting\accPlugin;

/*
 * Now starts the test pages you can use the routes below as an example to build your application
 */

$twig = $app->view()->getEnvironment();
$twig->addGlobal("site", $app->site);

$loader = $twig->getLoader();
$loader->addPath($app->config('plugins.path') . "/accplugin/template");

$app->get('/accplugin', function () use ($app) {
    // Access-controlled page
    if (!$app->user->checkAccess('uri_dashboard')) {
        $app->notFound();
    }
    $lproperties = ['htmlid' => 'cddtable_dt_1', 'dtjsvar' => 'cdDT001', 'role' => 'user'];

    require_once($app->config('plugins.path') . '/accplugin/controllers/cdUserDTController.php');
    $userdtcontroller = new \UserFrosting\accPlugin\cdUserDTController($app, $lproperties);
    $userdtcontroller->setupDatatable();
    $userdtcontroller->createDatatableHTMLJS();
    $userlist = $userdtcontroller->getDatatableArray();

    $lproperties2 = ['htmlid' => 'cddtable_dt_2', 'dtjsvar' => 'cdDT002', 'role' => 'user'];

    require_once($app->config('plugins.path') . '/accplugin/controllers/cdGroupDTController.php');
    $groupdtcontroller = new \UserFrosting\accPlugin\cdGroupDTController($app, $lproperties2);
    $groupdtcontroller->setupDatatable();
    $groupdtcontroller->createDatatableHTMLJS();
    $grouplist = $groupdtcontroller->getDatatableArray();

    $app->render('account-dashboard.html.twig', [
        'page' => [
            'author' => $app->site->author,
            'title' => "Dtatable Dashboard",
            'image_path' => "/cddt",
            'description' => "Your datatable dashboard.",
            'alerts' => $app->alerts->getAndClearMessages()
        ],
        "userlist" => $userlist,
        "grouplist" => $grouplist
        
    ]);
});

$app->post('/accplugin/:source/getdata', function ($source) use ($app) {
    // Access-controlled page

    if (!$app->user->checkAccess('uri_dashboard')) {
        $app->notFound();
    }
    $post = $app->request->post();

    switch ($source) {
        case 'UserListing': {
                $var_dtoptions = $post['dtoptions'];
                $var_dtoptions['role'] = 'user';
                $var_dtoptions['htmlid'] = $var_dtoptions['id'];
logarr($post, "Line 142 get params for data call");
                require_once($app->config('plugins.path') . '/accplugin/controllers/cdUserDTController.php');
//    $lproperties = ['htmlid' => 'cddtable_dt_1', 'dtjsvar' => 'cdDT001', 'role' => 'user'];
                $thisdtController = new \UserFrosting\accPlugin\cdUserDTController($app, $var_dtoptions);
                $thisdtController->setupDatatable();
//                $thisdtController->setWhereCriteria($var_where);
                $var_retjson = $thisdtController->populateDatatable();
                echo $var_retjson;
                break;
            }
        case 'GroupListing': {
                $var_dtoptions = $post['dtoptions'];
                $var_dtoptions['role'] = 'user';
                $var_dtoptions['htmlid'] = $var_dtoptions['id'];
logarr($post, "Line 142 get params for data call");
                require_once($app->config('plugins.path') . '/accplugin/controllers/cdGroupDTController.php');
//    $lproperties = ['htmlid' => 'cddtable_dt_1', 'dtjsvar' => 'cdDT001', 'role' => 'user'];
                $thisdtController = new \UserFrosting\accPlugin\cdGroupDTController($app, $var_dtoptions);
                $thisdtController->setupDatatable();
//                $thisdtController->setWhereCriteria($var_where);
                $var_retjson = $thisdtController->populateDatatable();
                echo $var_retjson;
                break;
            }
        default: {
                break;
            }
    }
});

$app->post('/accplugin/:source/editform', function ($source) use ($app) {
    // Access-controlled page

    if (!$app->user->checkAccess('uri_dashboard')) {
        $app->notFound();
    }
    $post = $app->request->post();

//echoarr($post);
//    echo "Here is your form for Source: $source ";
    switch ($source) {
        case 'UserListing': {
                $var_dtoptions = $post['dtoptions'];
                $var_dtoptions['role'] = 'user';
                $var_dtoptions['htmlid'] = $var_dtoptions['id'];
logarr($post, "Line 142 get params for data call");
                require_once($app->config('plugins.path') . '/accplugin/controllers/pluginUserController.php');
                $controller = new pluginUserController($app);
//                echo $controller->pageUser($post['id'],true);
                echo $controller->formUserEdit($post['id']);
                break;
            }
        case 'GroupListing': {
                break;
            }
        default: {
                break;
            }
    }
    
});


$var_savedatafunction = function ($source) use ($app) {
    // Access-controlled page
    $var_retjson = 'inside save';

    if (!$app->user->checkAccess('uri_dashboard')) {
        $app->notFound();
    }
    $var_dbinfo = setDBTableName($app->config('db')['db_prefix'], $source);
    $thisdtController = new cdDatatableFFController($app, $source, $var_dbinfo['db_table'], 'db', 'dummy', 'dummmy');

    $var_retjson = $thisdtController->storeDatatableRecord();
    echo $var_retjson;
};

$app->post('/accplugin/:source/savedata', $var_savedatafunction);
