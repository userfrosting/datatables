<?php

/*
  Plugin Name:  CapableData - DataTables Plugin v 0.1
  Description:  CapableData - DataTables Plugin v 0.1
  Author: Srinivas Nukala
  Version: 0.1
 */

include_once('common/dt_functions.php');
require_once('controllers/ufDatatableController.php');
require_once('models/ufDatatableSource.php');

$twig = $app->view()->getEnvironment();
$twig->addGlobal("site", $app->site);

$loader = $twig->getLoader();
$loader->addPath($app->config('plugins.path') . "/ufdatatables/views");

$app->hook('ufdt.css.register', function () use ($app) {
//    $app->schema->registerCSS("ufdatatable", $app->config('assets.path') . "/vendor/datatables/media/css/jquery.dataTables.min.css");
    $app->schema->registerCSS("ufdatatable", "/assets/vendor/datatables/media/css/jquery.dataTables.min.css");
    $app->schema->registerCSS("ufdatatable", "/assets/vendor/datatables/media/css/dataTables.bootstrap.min.css");
    $app->schema->registerCSS("ufdatatable", "/assets/vendor/datatables-responsive/css/responsive.bootstrap.scss");
    $app->schema->registerCSS("ufdatatable", "/assets/vendor/datatables-responsive/css/responsive.dataTables.scss");
    
}, 2);

$app->hook('ufdt.js.register', function () use ($app) {
    // Register common JS files
    $app->schema->registerJS("ufdatatable", "/assets/vendor/datatables/media/js/jquery.dataTables.min.js");
    $app->schema->registerJS("ufdatatable", "/assets/vendor/datatables/media/js/dataTables.bootstrap.min.js");
    $app->schema->registerJS("ufdatatable", "/assets/vendor/datatables-plugins/api/fnReloadAjax.js");
    $app->schema->registerJS("ufdatatable", "/assets/vendor/datatables-responsive/js/dataTables.responsive.js");
    
}, 2);

$app->applyHook("ufdt.css.register");
$app->applyHook("ufdt.js.register");


$var_twig_createdatatable = function ($source, $source_type, $htmlid, $dtjsvar,$show_detail,$ajax_detail) use ($app) {
    // Return array of JS includes
//    $var_dbinfo = setDBTableName($app->config('db')['db_prefix'], $source);
//logarr($var_dbinfo,"Line 68");    
    $thisdtController = new ufDatatableDBController($app, $source, 'uf_users', $source_type, $htmlid, $dtjsvar,$show_detail,$ajax_detail);
    $thisdtController->createDatatableHTMLJS();
//    $var_thistoken = $thisdtController->getDatatableToken();
    return $thisdtController->getDatatableArray();
};
$function_createDatatable = new \Twig_SimpleFunction('createDatatable', $var_twig_createdatatable);
$twig->addFunction($function_createDatatable);

$var_twig_createdatatable = function ($source, $table, $htmlid, $dtjsvar, $show_detail, $ajax_detail) use ($app) {
    // Return array of JS includes
    $source_type='DB';
    $thisdtController = new ufDatatableFFController($app, $source, $table, $source_type, $htmlid, $dtjsvar, $show_detail, $ajax_detail);
    $thisdtController->createDatatableHTMLJS();
//    $var_thistoken = $thisdtController->getDatatableToken();
    return $thisdtController->getDatatableArray();
};
$function_createDatatable = new \Twig_SimpleFunction('createDatatable', $var_twig_createdatatable);
$twig->addFunction($function_createDatatable);