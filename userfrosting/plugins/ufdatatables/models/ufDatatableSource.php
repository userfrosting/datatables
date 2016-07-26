<?php

namespace UserFrosting\ufDatatables;
//use Illuminate\Support\Facades\DB as EDB;
use \Illuminate\Database\Capsule\Manager as Capsule; 
//use Illuminate\Database\Capsule\Manager as EDB;
use Illuminate\Database\Eloquent\Model as Eloquent;
//use \Illuminate\Database\Connection as DB;
/**
 * UserLoaderInterface Interface
 *
 * Provides an interface for fetching User objects.  This can now be done directly through the User::find() method.
 *
 * Represents a static class for loading User object(s) from the database, checking for existence, etc.
 * @package UserFrosting
 * @author Alex Weissman
 * @see http://www.userfrosting.com/tutorials/lesson-3-data-model/
 * @deprecated deprecated since version 0.3.1
 */

class ufDatatableSource extends \UserFrosting\UFModel {

    protected static $_columns;     // A list of the allowed columns for this type of DB object. Must be set in the child concrete class.  DO NOT USE `id` as a column!
//    protected static $_table;       // The name of the table whose rows this class represents. Must be set in the child concrete class.    
    protected static $_last_sql_executed;       // The name of the table whose rows this class represents. Must be set in the child concrete class.    
    protected static $_filtered_records;       // The name of the table whose rows this class represents. Must be set in the child concrete class.    
    protected static $_total_records;  
    protected static $_dt_rowid='';
    protected static $_table_id = "undefined";

    public function __construct($properties=[], $id = null) {
        parent::__construct($properties, $id);
            
    }
    static function init($table) {
        // Set table and columns for this class.
//        static::$_table = $table;
        static::$_table_id = $table;
        static::$_filtered_records=0;       // The name of the table whose rows this class represents. Must be set in the child concrete class.    
        static::$_total_records=0;    
    }

    public static function setRowIdColumn($dtrowid='')
    {
        if($dtrowid!='')
        {
            static::$_dt_rowid = ", $dtrowid DT_RowId ";
        }
    }
    
    /**
     * Fetch a single record based on the value of a given column.
     *
     * For non-unique columns, it will return the first entry found.  Returns false if no match is found.
     * @param value $value The value to find.
     * @param string $name The name of the column to match (defaults to id)
     * @return ufDatatableSource
     */
    public static function fetch($value, $name = "id"){

        self::init(static::$_table_id);
        
        if ($name == "id")
            // Fetch by id
            $results_obj =  self::find($value);
        else
            // Fetch by some other column name
            $results_obj = self::where($name, $value)->first();
        if ($results)
        {
            $results=$results_obj->toArray();
            static::$_last_rows_found=count($results);
            return new ufDatatableSource($results, $results['id']);
        }
        else
            return false;
        
    }
    
    /**
     * Fetch a list of users based on the value of a given column.  Returns empty array if no match is found.
     *
     * @param value $value The value to find. (defaults to null, which means return all records in the table)
     * @param string $name The name of the column to match (defaults to null)
     * @return array An array of User objects
     */
// Srinivas Added additional where and order by clauses
    
    public static function fetchAll($value = null, $name = null,$par_where='',$par_orderby='',
            $par_limit='',$par_staticwhere=''){
        $table = static::$_table_id;
        
        // Check that the column name, if specified, exists in the table schema.
        if ($name && !in_array($name, static::$_table_id->columns))
            throw new \Exception("The column '$name' does not exist in the table '$table'.");
        
        $sqlVars = [];
//        $query = "SELECT SQL_CALC_FOUND_ROWS  * ".static::$_dt_rowid ." FROM `$table`";
        $query = "SELECT SQL_CALC_FOUND_ROWS  * ".static::$_dt_rowid ." FROM `$table`";
        if ($name) {
            $query .= " WHERE `$name` = '$value' ";
            $sqlVars[':value'] = $value;
            if($par_where !='')
            {
                $query .= " AND  $par_where ";
            }
            
        }
        else if($par_where !='')
        {
            $query = $query. " WHERE  $par_where ";
        }
        
        if($par_orderby !='')
        {
            $query .= " ORDER BY $par_orderby ";
        }

        if($par_limit !='')
        {
            $query .= " LIMIT  $par_limit ";
        }
        
        $results = Capsule::select($query); 
        
        $query2 = "SELECT FOUND_ROWS() as foundrows";
        $results2 = Capsule::select($query2); 
//logarr($results2,"Line 162 Query 2 results");

        $query3 = "SELECT count(*) total_count FROM `$table` ";
        
        if($par_staticwhere !='')
        {
            $query3 .= " WHERE  $par_staticwhere";
        }
        
//echobr("Line 106 $query3");        
        $results3 = Capsule::select($query3); 
        $var_retval = array();
        $var_retval['data']=$results;
        $var_retval['foundrows']=$results2[0]['foundrows'];
        $var_retval['totalrows']=$results3[0]['total_count'];
//logarr($var_retval,"Line 116 Query was $query");        
        return $var_retval;
    }

    
    public static function datatableFetchAll($value = null, $name = null, $par_where = '', $par_orderby = '', $par_limit = '',
            $par_staticwhere='') {
//error_log("Line 201 $value, $name, $par_where, $par_orderby,$par_limit,$par_staticwhere");        
        $resultArr_filtered_full = self::fetchAll($value, $name, $par_where, $par_orderby,$par_limit,$par_staticwhere);
//logarr($resultArr_filtered_full,"Line 185 filtered full");  
        $resultArr_filtered = $resultArr_filtered_full['data'];

        static::$_filtered_records=$resultArr_filtered_full['foundrows'];
        static::$_total_records =$resultArr_filtered_full['totalrows'];
        return $resultArr_filtered;
    }

    /**
     * Perform the SQL queries needed for an server-side processing requested,
     * utilising the helper functions of this class, limit(), order() and
     * filter() among others. The returned array is ready to be encoded as JSON
     * in response to an SSP request, or can be modified if needed before
     * sending back to the client.
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  array $sql_details SQL connection details - see sql_connect()
     *  @param  string $table SQL table to query
     *  @param  string $primaryKey Primary key of the table
     *  @param  array $columns Column information array
     *  @return array          Server-side processing response array
     * 
     * 
     * // This was old simple function
     */
    public static function getDatatableData($par_coldef,$request, $par_nondbcols = 'none', $par_where = '', $par_filter = '',$par_order = '') {
        $bindings = array();
//logarr($request,"Line 199 getDatatableData params $par_nondbcols = 'none', $par_where = '', $par_filter = ''");
        if ($par_nondbcols != 'none') {
            $var_nondbfield = implode(", ", $par_nondbcols) . ", ";
            $var_datacols_t = $var_datacols = array();
            foreach (array_keys($par_nondbcols) as $var_nondbcol) {
                $var_datacols_t['dt'] = $var_nondbcol;
                $var_datacols_t['db'] = $var_nondbcol;
                $var_datacols[] = $var_datacols_t;
            }
        } else {
            $var_nondbfield = '';
            $var_datacols = '';
        }
        // Build the SQL query string from the request
        $limit = self::limit($request);
        $order = self::order($par_coldef, $request);
        $where = self::filter($par_coldef, $request, $bindings);

//        $var_staticwhere= $par_where;

        if ($where != '') {
            if ($par_where != '')
                $where = "($where) AND ( $par_where )";
        }
        else {
            if ($par_where != '')
                $where = "  $par_where ";
        }

        if ($where != '') {
            if ($par_filter != '')
                $where.= " AND  $par_filter ";
        }
        else {
            if ($par_filter != '')
                $where = "  $par_filter ";
        }
        
        if($par_order !='')
        {
            $order = $order==''?$par_order:" $order, $par_order ";
        }
        if ($order != '') {
            if ($par_filter != '')
                $where.= " AND  $par_filter ";
        }

        $var_retval = array();
//error_log("Line 238: Datatable fetch all params  $where, $order, $limit,$par_where");        
        $var_retval['records'] = self::datatableFetchAll(null, null, $where, $order, $limit,$par_where);
        $var_retval['filtered_count'] = static::$_filtered_records;
        $var_retval['total_count'] = static::$_total_records;
        
        return $var_retval;
    }

    /**
     * Paging
     *
     * Construct the LIMIT clause for server-side processing SQL query
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  array $columns Column information array
     *  @return string SQL limit clause
     */
    private static function limit($request) {
        $limit = '';

        if (isset($request['start']) && $request['length'] != -1) {
            $limit = " " . intval($request['start']) . ", " . intval($request['length']);
        }

        return $limit;
    }

    /**
     * Ordering
     *
     * Construct the ORDER BY clause for server-side processing SQL query
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  array $columns Column information array
     *  @return string SQL order by clause
     */
    private static function order($par_coldef,$request) {
        $order = '';

        if (isset($request['order']) && count($request['order'])) {
            $orderBy = array();
            $dtColumns = self::pluck($par_coldef, 'dt');

            for ($i = 0, $ien = count($request['order']); $i < $ien; $i++) {
                // Convert the column index into the column data property
                $columnIdx = intval($request['order'][$i]['column']);
                $requestColumn = $request['columns'][$columnIdx];
//echoarr($requestColumn,"Line 103 Index $columnIdx");
                if (isset($requestColumn['data']['sort']))
                    $var_scol = $requestColumn['data']['sort'];
                else
                    $var_scol = $requestColumn['data'];

                $columnIdx = array_search($var_scol, $dtColumns);
//				$columnIdx = array_search( $requestColumn['data'], $dtColumns );
                $column = $par_coldef[$columnIdx];

                if ($requestColumn['orderable'] == 'true') {
                    $dir = $request['order'][$i]['dir'] === 'asc' ?
                            'ASC' :
                            'DESC';

//                    $orderBy[] = '`' . $column['db'] . '` ' . $dir;
                    $orderBy[] = $column['db'] ." ". $dir;
                }
            }

            if (count($orderBy) > 0)
                $order = ' ' . implode(', ', $orderBy);
        }

        return $order;
    }

    /**
     * Searching / Filtering
     *
     * Construct the WHERE clause for server-side processing SQL query.
     *
     * NOTE this does not match the built-in DataTables filtering which does it
     * word by word on any field. It's possible to do here performance on large
     * databases would be very poor
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  array $columns Column information array
     *  @param  array $bindings Array of values for PDO bindings, used in the
     *    sql_exec() function
     *  @return string SQL where clause
     */
    private static function filter($par_coldef,$request, &$bindings) {
        $globalSearch = array();
        $columnSearch = array();
        $dtColumns = self::pluck($par_coldef, 'dt');
//logarr($dtColumns,"Line 331 inside Filter");
//logarr($par_coldef,)
        if (isset($request['search']) && $request['search']['value'] != '') {
            $str = $request['search']['value'];

            for ($i = 0, $ien = count($request['columns']); $i < $ien; $i++) {
                $requestColumn = $request['columns'][$i];
                $columnIdx = array_search($requestColumn['data'], $dtColumns);
                $column = $par_coldef[$columnIdx];

//logarr($requestColumn,"Line 341 checking for Searchagble == 'Y')".(gettype($requestColumn['searchable'])));                
                if ($requestColumn['searchable'] == 'Y'|| $requestColumn['searchable'] == 'true') {
//					$binding = self::bind( $bindings, '%'.$str.'%', PDO::PARAM_STR );
//					$globalSearch[] = "`".$column['db']."` LIKE ".$binding;
//                    $globalSearch[] = "`" . $column['db'] . "` LIKE '%" . $str . "%'";
                    $globalSearch[] =  $column['db'] . " LIKE '%" . $str . "%'";
                }
            }
        }
//logarr($globalSearch,"Line 350 global serach array");
        // Individual column filtering
        for ($i = 0, $ien = count($request['columns']); $i < $ien; $i++) {
            $requestColumn = $request['columns'][$i];
            $columnIdx = array_search($requestColumn['data'], $dtColumns);
            $column = $par_coldef[$columnIdx];

            $str = $requestColumn['search']['value'];

            if ($requestColumn['searchable'] == 'Y' && $str != '') {
//				$binding = self::bind( $bindings, '%'.$str.'%', PDO::PARAM_STR );
//				$columnSearch[] = "`".$column['db']."` LIKE ".$binding;
//                $columnSearch[] = "`" . $column['db'] . "` LIKE '%" . $str . "%'";
                $columnSearch[] =  $column['db'] . " LIKE '%" . $str . "%'";
            }
        }

        // Combine the filters into a single string
        $where = '';

        if (count($globalSearch)) {
            $where = '(' . implode(' OR ', $globalSearch) . ')';
        }

        if (count($columnSearch)) {
            $where = $where === '' ?
                    implode(' AND ', $columnSearch) :
                    $where . ' AND ' . implode(' AND ', $columnSearch);
        }
        return $where;
    }

    
    /*     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Internal methods
     */

    /**
     * Throw a fatal error.
     *
     * This writes out an error message in a JSON string which DataTables will
     * see and show to the user in the browser.
     *
     * @param  string $msg Message to send to the client
     */
    private static function fatal($msg) {
        echo json_encode(array(
            "error" => $msg
        ));

        exit(0);
    }

    /**
     * Create a PDO binding key which can be used for escaping variables safely
     * when executing a query with sql_exec()
     *
     * @param  array &$a    Array of bindings
     * @param  *      $val  Value to bind
     * @param  int    $type PDO field type
     * @return string       Bound key to be used in the SQL where this parameter
     *   would be used.
     */
    private static function bind(&$a, $val, $type) {
        $key = ':binding_' . count($a);

        $a[] = array(
            'key' => $key,
            'val' => $val,
            'type' => $type
        );

        return $key;
    }

    /**
     * Pull a particular property from each assoc. array in a numeric array, 
     * returning and array of the property values from each item.
     *
     *  @param  array  $a    Array to get data from
     *  @param  string $prop Property to read
     *  @return array        Array of property values
     */
    private static function pluck($par_coldef,$prop) {
        $out = array();

        for ($i = 0, $len = count($par_coldef); $i < $len; $i++) {
            $out[] = $par_coldef[$i][$prop];
        }

        return $out;
    }
    
}
