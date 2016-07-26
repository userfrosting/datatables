<?php
namespace UserFrosting\ufDatatables;
/**
 * ufDatatableController
 *
 * @package UserFrosting Datatables
 * @author Srinivas Nukala
 * @link http://srinivasnukala.com
 */
class ufDatatableController extends \UserFrosting\BaseController {
    protected $_source;       // table that this datatable will use to query
    protected $_source_type;       // table that this datatable will use to query
    protected $_htmlid;       // table that this datatable will use to query
    protected $_dtjsvar;
    protected $_token;       // table that this datatable will use to query
    protected $_fields;       // source fields data model
    protected $_data;       // source data model
    protected $_datatable;

    protected $_show_detail;
    protected $_ajax_detail;
    
    protected $_datatable_html_twig = 'datatable.html.twig';
    protected $_datatable_js_twig = 'datatable.js.twig';
    
    protected $_formatters=[];
    protected $_data_route='getdata';
    protected $_process_route='process';

    protected $_db_table;       // table that this datatable will use to query
    protected $_db_columns;       // columns that this datatable will use to query
    protected $_primary_key = 'id';       // table that this datatable will use to query
    protected $_role = 'guest';
    protected $_where_criteria = '';       // where criteria, dynamicall set
    protected $_order_by = '';       // Order by column

    /**
     * constructor
     *
     * @param object $app app object.
     * @return none.
     */
    public function __construct($app, $properties=[]) {
        parent::__construct($app);
        $this->setProperties($properties);
    }

    public function setProperties($properties=[]) {
        $this->_source = valueIfSet($properties,'source',$this->_source);
        $this->_db_table = valueIfSet($properties,'dbtable',$this->_db_table);
        $this->_source_type = valueIfSet($properties,'source_type',$this->_source_type);
        $this->_htmlid = valueIfSet($properties,'htmlid',$this->_htmlid);
        $this->_dtjsvar = valueIfSet($properties,'dtjsvar',$this->_dtjsvar);
        $this->_show_detail=valueIfSet($properties,'show_detail',$this->_show_detail);
        $this->_ajax_detail=valueIfSet($properties,'ajax_detail',$this->_ajax_detail);
        $this->_role=valueIfSet($properties,'role',$this->_role);
    }

    public function setupDatatable() {
        $this->createDatatableToken();
        $this->setFormatters();
    }
    
    public function getWhereCriteria()
    {
        return $this->_where_criteria;
    }

    public function setWhereCriteria($where)
    {
        $this->_where_criteria = $where;
    }

    public function getOrderBy()
    {
        return $this->_order_by;
    }

    public function setOrderBy($order)
    {
        $this->_order_by = $order;
    }

    public function setRole($role) {
//error_log("Line 55 setting user role to $role");
        $this->_role = $role;
        // will be used by the child classes to set the formatters for various columns
    }

    public function getRole() {
        return $this->_role;
        // will be used by the child classes to set the formatters for various columns
    }
    
    public function postDatatableInit()
    {
        $this->setDatatableDefaultOptions();
    }

    public function createDatatableHTMLJS()
    {
        $this->createDatatableHTML();
        $this->createDatatableJS();
        
    }

    private function createDatatableToken() {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $rand_num = openssl_random_pseudo_bytes(16); //pull 16 bytes from /dev/random
        } else {
            /*
              RYO(Roll Your Own) random number gen.
              only used in the event openssl isn't available
             */
            $rand = array();
            for ($i = 0; $i < 64; $i++) {
                $random = mt_rand(rand(0, 65012), mt_getrandmax()); //get a random number between rand(0,65012) and mt rand max
                $rand[$i] = mt_rand($i, $random); //add an array key of $i and a value of a number between $i and the first random number
            }
            $rand = array_sum($rand); //shuffle the random number, then sum the values
            $rand_num = str_shuffle($rand * 64); //multiply the rand number by 64 and shuffle the string.
        }
        if (isset($rand_num)) {
            $build_string = $rand_num . serialize($_SERVER) . time();
            if (isset($build_string)) {
                $token = hash('whirlpool', str_shuffle($build_string));
            } else {
                throw new \Exception('Could not generate a random number for the CSRF token!');
            }
        } else {
            throw new \Exception('Could not generate a random number for the CSRF token!');
        }
        $this->_token = $token; //sha1(serialize($_SERVER) . rand(0, 0xffffffff));
    }

    public function getDatatableOptions() {
        return $this->_datatable['options'];
    }

    public function setDatatableOption($option,$optvalue) {
        $this->_datatable['options'][$option]=$optvalue;
    }

    public function getDatatableToken() {
        return $this->_token;
    }

    public function setFormatters()
    {
        // will be used by the child classes to set the formatters for various columns
    }
    
    public function getColumnDefinitions()
    {
        // will be used by the child classes to set the formatters for various columns
    }

    public function createJSFile() {
        $this->_app->response->headers->set("Content-Type", "application/javascript");
        $this->_app->response->setBody($this->_datatable['js']);
    }

    public function getDatatableArray() {
        $this->_datatable['htmlid']=$this->_htmlid;
        return $this->_datatable;
    }
    public function createDatatableHTML() {
        
        $var_dtinfo = array();
        $var_dtinfo['htmlid']=$this->_htmlid;
        $var_dtinfo['colspan']=$this->_datatable['column_count'];
//logarr($var_dtinfo,"Line 109 datatable html render");        
        $this->_datatable['html'] = $this->_app->view->fetch($this->_datatable_html_twig, [
            'page' => [
                'author' => $this->_app->site->author,
                'title' => "Datatable",
                'image_path' => "/images",
                'description' => "Your user dashboard.",
                'alerts' => $this->_app->alerts->getAndClearMessages()
            ],
            'dtinfo' => $var_dtinfo
        ]);
    }

    public function createDatatableJS() {
//logarr($this->_datatable['options'],"Line 123 datatable options and the JS file is ".$this->_datatable_js_twig);    
//error_log("Line 163 datatable options and the JS file is ".$this->_datatable_js_twig);    
        
        $this->_datatable['js'] = $this->_app->view->fetch($this->_datatable_js_twig, [
            'page' => [
                'author' => $this->_app->site->author,
                'title' => "Datatable",
                'image_path' => "/images",
                'description' => "Your user dashboard.",
                'alerts' => $this->_app->alerts->getAndClearMessages()
            ],
            'dtoptions' => $this->_datatable['options']]
        );
//error_log("Line 175 the js file contents are ".$this->_datatable['js']);        
    }

    public function setPrimaryKeyFormatter()
    {
        if ($this->_ajax_detail == 'N') {
            $var_pkformatter = function( $d, $row ) {
                $var_ret = "<a class='edit_row' onClick='onClickEditRow(\"$this->_htmlid\",this);return false;'>ID-$d</a>";
                return $var_ret;
            };
        } else {
            $var_pkformatter = function( $d, $row ) {
                $var_ret = "<a class='edit_row' onClick='onClickAjaxEditRow(\"$this->_htmlid\",this,$d);return false;'>Ajax ID-$d</a>";
                return $var_ret;
            };
        }
        return $var_pkformatter;
    }
    
    
    private function setDatatableDefaultOptions() {
        $this->_datatable['options'] = array("htmlid" => $this->_htmlid,
            "dtjsvar" => $this->_dtjsvar,
            "show_detail" => $this->_show_detail,
            "ajax_detail" => $this->_ajax_detail,
//            "ajax_url" => "/".$this->_data_route."/" . $this->_source,
            "ajax_url" => "/".$this->_data_route,
//            "process_url" => "/".$this->_process_route."/" . $this->_source,
            "process_url" => "/".$this->_process_route,
            "source" => $this->_source,
            "pagelength" => "10",
            "thispage" => "1",
            "extra_param" => "",
            "responsive" => "N",
            "scroll" => "N",
            "_dt_rowid"=>'',
            "scrollsize" => "0",
            "column_definition" => "",
            "swf_path" => "/swf",
            "initial_search" => "",
            'fields' => $this->_fields,
            'all_columns' => $this->_datatable['all_columns'],
            'colspan' => $this->_datatable['column_count']);
//logarr($this->_datatable['options'],"Line 186");        
        
    }
    
    protected function setDatatableParameters($par_tabdef) {
        $var_colspan = 0;
        $var_datatable_cols = $var_allcols = array();
        foreach ($par_tabdef as &$var_column) {
//logarr($var_column,"Line 980 tabdef");
            $var_colspan+=$var_column['visible'] ? 0 : 1;
            $var_fcol = array();
            $var_fcol['db'] = $var_column['name'];
            $var_fcol['dt'] = $var_fcol['db'];
            $var_column["data"] = $var_column['name'];
            
            if(isset($this->_formatters[$var_column['name']]))
            {
                $var_fcol['formatter']=$this->_formatters[$var_column['name']];
            }
            else
            {
                if ($var_column['type'] == 'date' || $var_column['type'] == 'datetime') {
                    $var_fcol['formatter'] = function( $d, $row ) {
                        if($d!='')
                            return date('D jS \of M Y h:i A', strtotime($d));
                        else
                            return $d;
                    };
                }
    //echobr($this->_dtjsvar." Line 181");
                if ($this->_show_detail == 'Y') {
                    if (isset($var_column['primary_key']) && $var_column['primary_key']) {
    //echobr("Line 69 ".$this->_ajax_detail);
                        $var_fcol['formatter'] = $this->setPrimaryKeyFormatter();
                    }
                }
            }
            $var_datatable_cols[] = $var_fcol;
            $var_allcols[$var_column['name']] = $var_column['name'];
        }
        $this->_fields = $par_tabdef;
        $this->_datatable['all_columns'] = $var_allcols;
        $this->_datatable['column_count'] = $var_colspan;
        $this->_datatable['column_data_def'] = $var_datatable_cols;
//logarr( $this->_datatable,"Line 91 datatable variable");        
    }
    
    public function getDataFromSource($getparam,$par_nondbcols = 'none', $par_where = '', $par_filter = '',$par_order='')
    {
            $table_dtsource = new \UserFrosting\DatabaseTable($this->_db_table, $this->_db_columns);
            \UserFrosting\Database::setSchemaTable($this->_db_table, $table_dtsource);
            ufDatatableSource::init($this->_db_table);
//logarr($getparam,"Line 112 inside DB Datatable controller, $par_nondbcols = 'none', $par_where = '', $par_filter = '' Table ".$this->_db_table);            
//logarr($this->_db_columns,"Line 113 columns");
//logarr($this->_datatable['column_data_def'],"Line 115 column def");
//            $var_retarr = $this->simple($getparam, $par_nondbcols, $par_where, $par_filter);
//error_log("Line 143 setting dtrowid ".$this->_datatable['options']['_dt_rowid']) ;           
            ufDatatableSource::setRowIdColumn($this->_datatable['options']['_dt_rowid']);
            $var_retdata = ufDatatableSource::getDatatableData($this->_datatable['column_data_def'], 
                    $getparam, $par_nondbcols, $par_where, $par_filter,$par_order);
     
            $this->_data['records'] = $var_retdata['records'];
            $this->_data['filtered_count'] = $var_retdata['filtered_count'];
            $this->_data['total_count'] = $var_retdata['total_count'];
            
            return $var_retdata;
    }

    public function populateDatatable($par_nondbcols = 'none', $par_where = '', $par_filter = '') {
        
    // Access-controlled page
    $requestSchema = new \Fortress\RequestSchema($this->_app->config('plugins.path') . "/ufdatatables/schema/forms/dt-home.json");

        // Get the alert message stream
        $ms = $this->_app->alerts; 
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $this->_app->request->post());
        // Sanitize data
        $rf->sanitize();
        // Validate, and halt on validation errors.
        if (!$rf->validate(true)) {
            $this->_app->halt(400);
        }
        // Get the filtered data
        $getparam = $rf->data();

//logarr($getparam, "Line 215 the datatable POST parameters are ");        
        
        
//        $getparam = $this->_app->request->get();
        
        $this->getDataFromSource($getparam,$par_nondbcols, $par_where, $par_filter);

        $var_retarr = $this->createOutputJSONArray($getparam['draw']);
//echoarr($var_retarr,"Line 291");                
//        return $var_retarr;
        $var_retval = json_encode((array) $var_retarr);
//echobr("Line 304 $var_retval. ERROR is  ".json_last_error());  
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
//            echo ' - No errors';
                break;
            case JSON_ERROR_DEPTH:
                $var_retval = ' - Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $var_retval = ' - Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $var_retval = ' - Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $var_retval = ' - Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $var_retval = ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $var_retval = ' - Unknown error';
                break;
        }
        return $var_retval;
    }

    

    public function storeDatatableRecord() {

        $var_post = $this->_app->request->post();
//logarr($var_post,"Line 140 post array in datatable processing");        
//echoarr($var_post);        
        $table_dtsource = new \UserFrosting\DatabaseTable($this->_db_table, $this->_db_columns);
        \UserFrosting\Database::setSchemaTable($this->_db_table, $table_dtsource);
        ufDatatableSource::init($this->_db_table);
// if we find a record with the UID then, update the record 
        ufDatatableSource::init($this->_db_table); 
        $var_updobj = new ufDatatableSource($var_post['erec'], $var_post['erec']['id']);
//        $var_updobj->init($this->_db_table); 
        if($var_post['erec']['id'] > 0)
            $var_updobj->exists = true;
//        $result_obj = $var_updobj->where('id', $var_post['erec']['id'])->get();
//logarr($result_obj,"line 158");
// Save to database
        $var_updobj->store();
        return "Save successful";
    }

    public function createOutputJSONArray($par_draw) {

//count($this->_db_table), count($this->_db_table), $var_cols, $this->_db_table        
//echoarr($par_datacols,"Line 299 data cols ");            
//echoarr($par_data,"Line 300 data  ");            
        $this->createDatatableOutput();
        $var_retarr = array(
            "draw" => intval($par_draw),
            "recordsTotal" => intval($this->_data['total_count']),
            "recordsFiltered" => intval($this->_data['filtered_count']),
            "aaData" => $this->_datatable['output_data']
        );
        return $var_retarr;
    }

    /**
     * Create the data output array for the DataTables rows
     *
     *  @param  array $columns Column information array
     *  @param  array $data    Data from the SQL get
     *  @return array          Formatted data in a row based format
     */
    public function createDatatableOutput() {
//    data_output($data) {
//echoarr($data, "Line 36 data array") ;           
//echoarr($columns, "Line 36 column array") ;           
        $out = array();

// fot future use : if we add custom columns that are not in the database        
//        if ($var_datacols != '') {
//            $var_datacols = array_merge($this->_datatable['column_data_def'], $var_datacols);
//        } else
//            $var_datacols = $this->_datatable['column_data_def'];

//echoarr($var_datacols,"Line 291 ");                

        foreach ($this->_data['records'] as $var_datarec) {
///echoarr($var_datarec);            
            $row = array();
//logarr($this->_datatable['column_data_def'],"Line 332 the coldef array");            
            foreach ($this->_datatable['column_data_def'] as $var_coldef) {
//echoarr($var_coldef);                
                if (isset($var_coldef['formatter'])) {
//                                    echobr("Line 46 formatter is set");
                    $row[$var_coldef['dt']] = $var_coldef['formatter']($var_datarec[$var_coldef['db']], $var_datarec);
                } else {
                    $row[$var_coldef['dt']] = $var_datarec[$var_coldef['db']];
                }
            }

            $out[] = $row;
        }
        $this->_datatable['output_data'] = $out;
    }
}