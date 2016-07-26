<?php

namespace UserFrosting\accPlugin;

/**
 * cdGroupDTController
 *
 * @package UserFrosting-Datatables
 * @author Srinivas Nukala
 * @link http://srinivasnukala.com
 */
class cdGroupDTController extends \UserFrosting\ufDatatables\ufDatatableController {

    /**
     * constructor
     *
     * @param object $app app object.
     * @return none.
     */
    public function __construct($app, $properties = []) {
        $properties['source'] = 'GroupListing';
        $properties['source_type'] = 'db';
        $properties['show_detail'] = 'Y';
        $properties['ajax_detail'] = 'N';
        $properties['dbtable'] = $app->config('db')['db_prefix']."group";
        $this->_data_route = 'accplugin/GroupListing/getdata';
        $this->_process_route = 'accplugin/GroupListing';
//        $this->_datatable_js_twig = '05lba/lba_datatable.js.twig';
//error_log("Line 29 Calling construct now the dbtable is $dbtable");        
        parent::__construct($app, $properties);
//logarr($this->_datatable['options'],"Line 37");        
    }

    public function setupDatatable() {
        parent::setupDatatable();
        $cur_ff_table = $this->getColumnDefinitions();
        $this->setDatatableParameters($cur_ff_table['fields']);

        $this->_datatable['options']["ajax_url"] = "/accplugin/getdata/groups";
        $this->_datatable['options']["process_url"] = "/accplugin";
        $this->_datatable['options']['_dt_rowid'] = " concat('group_',id) ";
        $this->_db_columns = array_keys($this->_fields);
        $this->postDatatableInit();
}

    public function getColumnDefinitions() {

// this is the list of atrributes you can set        
//["padding"=>"","defaultContent"=>"","name"=>"","orderable"=>"","searchable"=>"","title"=>"","type"=>"",
//"visible"=>"","class"=>"","width"=>"","data"=>"","default"=>"","render"=>"","showin_editform"=>"",
//"lookup_options"=>""]        
        $var_userdef=[];
        $var_userdef['primary_key']='id'; //this is the primary key fiend
// these are the fields that will be available for search        
//        $var_userdef['search_fields']=['id'=>1.10,'user_name'=>1.20,'display_name'=>1.30,'email'=>'1.40','title'=>1.50];
// these are the fields that will be available for view / edit        
//        $var_userdef['edit_fields']=['id'=>1.10,'user_name'=>1.20,'display_name'=>2.10,'email'=>2.20,
//            'title'=>3.10,'locale'=>3.20,'flag_enabled'=>4.1,'flag_verified'=>4.2];
        $var_ucoldef=[];
        $var_ucoldef['id']=["primary_key"=>true, "name"=>"id","orderable"=>true,"searchable"=>true,"title"=>"Group ID", "type"=>"text","visible"=>true,"showin_editform"=>true];
        $var_ucoldef['name']=["name"=>"name","orderable"=>true,"searchable"=>true,"title"=>"Group Name", "type"=>"text","visible"=>true,"showin_editform"=>true];
        $var_ucoldef['theme']=["name"=>"theme","orderable"=>true,"searchable"=>true,"title"=>"Theme", "type"=>"text","visible"=>true,"showin_editform"=>true];
        $var_ucoldef['landing_page']=["name"=>"landing_page","orderable"=>true,"searchable"=>true,"title"=>"Landing Page", "type"=>"email","visible"=>true,"showin_editform"=>true];
        $var_ucoldef['new_user_title']=["name"=>"new_user_title","orderable"=>true,"searchable"=>true,"title"=>"New User Title", "type"=>"text","visible"=>true,"showin_editform"=>true];
        $var_ucoldef['icon']=["name"=>"icon","orderable"=>false,"searchable"=>false,"title"=>"Icon", "type"=>"text","visible"=>false,"showin_editform"=>true];
        $var_ucoldef['is_default']=["name"=>"is_default","orderable"=>false,"searchable"=>false,"title"=>"Is Default", "type"=>"text","visible"=>false,"showin_editform"=>true];
        $var_ucoldef['can_delete']=["name"=>"can_delete","orderable"=>false,"searchable"=>false,"title"=>"Can Delete", "type"=>"text","visible"=>false,"showin_editform"=>true];
        $var_userdef['fields']=$var_ucoldef;
        
        return $var_userdef;
        // will be used by the child classes to set the formatters for various columns
    }

    public function setPrimaryKeyFormatter() {
        if ($this->_ajax_detail == 'Y') 
        {
            $var_pkformatter = function( $d, $row ) {
                $var_ret = "<a class='edit_row' onClick='onClickAjaxEditRow(\"$this->_htmlid\",this,$d);return false;'>"
                        . "Task-" . $row['task_seq'] . "</a>";
                return $var_ret;
            };
        }
        else{
            $var_pkformatter = function( $d, $row ) {
                return $d;
            };
        }
            return $var_pkformatter;
    }

    public function setFormatters() {
        parent::setFormatters();

//        error_log("Line 90 checking user role");
        $this->_formatters['due_date'] = function( $d, $row ) {
            if ($d != '')
                return date('D jS M Y', strtotime($d));
            else
                return $d;
        };


        $this->_formatters['task_name'] = function( $d, $row ) {
            $var_lookup = [];
            $var_lookup['key'] = '';
            $var_lookup['value'] = '';

            $var_lookup['data'] = $this->_app->lookup_cache['task_status'];
            $var_selbox = $this->_app->view->fetch('06tasks/tasks-edit-dt.html.twig', ['row' => $row,
                'statuslookup' => $this->_app->lookup_cache['task_status'],
                'task_data' => $row['task_data'],
                'dthtmlid' => $this->_htmlid,
                "taskeditdiv" => $this->_htmlid . "edit_i",
//                "task_files"=>$var_taskfiles,
                'ilookup' => $var_lookup]);
            return $var_selbox;
        };
    }

    public function getDataFromSource($getparam, $par_nondbcols = 'none', $par_where = '', $par_filter = '', $par_order = '') {
        $par_where = $this->_where_criteria;
        $par_order = $this->_order_by;
        error_log("Line 96 the where criteria is $par_where, order by is $par_order");
        parent::getDataFromSource($getparam, $par_nondbcols, $par_where, $par_filter, $par_order);

// This will set $this->_data These 3 arrays will be available if we want to change anything before the data gets sent out
//             
//            $this->_data['records'] = $var_retdata['records'];
//            $this->_data['filtered_count'] = $var_retdata['filtered_count'];
//            $this->_data['total_count'] = $var_retdata['total_count'];
    }

}
