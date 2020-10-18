<?php
/*
* Created by: @the-beta-core / Rik Janssen
* Version: 0.12
*/

class db{

    // I've tried to create a very simple class for basic 
    // database handling here. It contains some basic CRUD
    // functionalities without having to input SQL.
    // but if you need to, just use $?->sql();

    // The database credentials for the live database
    protected $db_live_dbname = 'crud_test_live';
    protected $db_live_user = 'root';
    protected $db_live_password = '';
    protected $db_live_host = 'localhost';

    // The database credentials for the staging database
    protected $db_stage_dbname = '';
    protected $db_stage_user = '';
    protected $db_stage_password = '';
    protected $db_stage_host = 'localhost';

    // Are we working on the live DB?
    protected $db_production = 'live'; // set to 'stage' or 'live'

    // Some properties that we need
    protected $db; 
    protected $sql_ran;
    public $db_error = false;
    public $db_works = true;

    //////////////////////////////////////////

    public function __construct(){

        // Are we running a staging site
        if($this->db_production=='stage'):

            $host = $this->db_stage_host;
            $user = $this->db_stage_user;
            $password = $this->db_stage_password;
            $dbname = $this->db_stage_dbname;

        // Or a live site?
        elseif($this->db_production=='live'):

            $host = $this->db_live_host;
            $user = $this->db_live_user;
            $password = $this->db_live_password;
            $dbname = $this->db_live_dbname;

        // Or maybe even no site at all.....
        else:

            $this->db_error =  "Select a database";
            $this->db_works = false;
            return;

        endif;

        // open the database connection
        $db = new mysqli($host, $user, $password, $dbname);

        // Check connection
        if ($db->connect_error) {
          $this->db_error = $conn->connect_error;
          $this->db_works = false;
          return;
        }
        
        // Keep the DB connection within the class for
        // further use...
        $this->db = $db;
        

    }

    public function close(){

        // Prevent the thing from running if the database is not working well
        if($this->db_works = false){ return; }

        // when done running, close the database connection
        mysqli_close($this->db);
        $this->db_error = "NoError: Database connection closed.";

    }

    //////////////////////////////////////////

    // Insert things into the database using an array of arguments:
    /*
    $args = array(
        'table' => 'Persons',
        'fields' => array(
            'LastName' => array(
                'value' => 'My Name'.rand(0,3000), 
                'sanitize' => 'text', 
                'placeholder'=>'My other name'
            ),
            'FirstName' => array(
                'value' => 'Hank', 
                'sanitize' => 'text'
            ),
            'Address' => array(
                'value' => 'My Address street', 
                'sanitize' => 'text',
                'placeholder'=>'My other street'
            ),
            'Number' => array(
                'value' => rand(0,3000), 
                'sanitize' => 'int'
            ),
            'City' => array(
                'value' => 'Citytowns', 
                'sanitize' => 'email'
            )
        ) // end fields
    );
    */
    public function insert($args){

        // Prevent the thing from running if the database is not working well
        if($this->db_works = false){ return; }

        // Set up a SQL string:
        $start = "INSERT INTO ";
        $fields = "";
        $values = "";

        // Then check all the arguments:

        // 1. Table name
        $t['table'] = $this->sanitize($args['table'],'string');
        $start .= $t['table'];

        // 2. Fields
        foreach($args['fields'] as $key => $val){

            // check if the field is filled or apply the placeholder
            if(!isset($val['placeholder'])){ $val['placeholder'] = ''; }
            if($val['value']==''){ $val['value'] = $val['placeholder']; }

            // sanitize the run and put it in an array
            $key          = $this->sanitize($key,'string');
            $t['f'][$key] = $this->sanitize($val['value'],$val['sanitize']);

            // Create the fields and values
            $fields .= $key.",";
            $values .= "'".$t['f'][$key]."',";

        }

        $fields = rtrim($fields,',');
        $values = rtrim($values,',');

        // 3. Create the final SQL string and RUN!
        $sql = $start ." (". $fields . ") VALUES (".$values.");";

        // Run the SQL
        $this->sql($sql);

    }

    //////////////////////////////////////////

    // Update things in the database using an array of arguments:
    /*
    $args = array(
        'table' => 'Persons',
        'set' => array(
            'LastName' => array(
                'value' => "Another Last Name!",
                'sanitize' => "text"
            ),
            'City' => array(
                'value' => "Another Town!",
                'sanitize' => "text"
            )
        ),
        'where' => array(
            'id' => array(
                'value' =>2 ,
                'sanitize' => 'int'
            )
        )
    );
    */

    public function update($args){

        // Prevent the thing from running if the database is not working well
        if($this->db_works = false){ return; }

        // Set up a SQL string:
        $start = "UPDATE ";
        $set = "";
        $where = "";

        // 1. Table name
        $t['table'] = $this->sanitize($args['table'],'string');
        $start .= $t['table'];

        // 2. Set
        // Check if set is set
        if(!isset($args['set'])){ return; }

        // If set, update
        foreach($args['set'] as $key => $val){

            // sanitize the run and put it in an array
            $key          = $this->sanitize($key,'string');
            $t['s'][$key] = $this->sanitize($val['value'],$val['sanitize']);

            // Create the fields and values
            $set .= $key."='".$t['s'][$key]."',";

        }

        $set = " SET ".rtrim($set,',');

        // 3. Where
        // Check if where is set
        if(!isset($args['where'])){ return; }

        // If set, update
        foreach($args['where'] as $key => $val){

            // sanitize the run and put it in an array
            $key          = $this->sanitize($key,'string');
            $t['w'][$key] = $this->sanitize($val['value'],$val['sanitize']);

            // Create the fields and values
            $where .= $key."='".$t['w'][$key]."',";

        }

        $where = " WHERE ".rtrim($where,',');

        // 4. Assemble the SQL string and run:
        $sql = $start . $set . $where.";";

        // Run the SQL
        $this->sql($sql);
        

    }

    //////////////////////////////////////////

    // Delete things from the database using an array of arguments:
    /*
    $args = array(
        'table' => 'Persons',
        'where' => array(
            'id' => array(
                'value' => rand(1,22),
                'sanitize' => 'int'
            )
        )
    );
    */
    public function delete($args){

        // Prevent the thing from running if the database is not working well
        if($this->db_works = false){ return; }

        // Set up a SQL string:
        $start = "DELETE FROM ";
        $where = "";

        // 1. Table name
        $t['table'] = $this->sanitize($args['table'],'string');
        $start .= $t['table'];

        // 2. Where
        // Check if where is set
        if(!isset($args['where'])){ return; }

        // If set, update
        foreach($args['where'] as $key => $val){

            // sanitize the run and put it in an array
            $key          = $this->sanitize($key,'string');
            $t['w'][$key] = $this->sanitize($val['value'],$val['sanitize']);

            // Create the fields and values
            $where .= $key."='".$t['w'][$key]."',";

        }

        $where = " WHERE ".rtrim($where,',');

        // 3. Assemble the SQL string and run:
        $sql = $start . $where.";";

        // Run the SQL
        $this->sql($sql);

    }

    //////////////////////////////////////////

    // Get things from the database using an array of arguments:
    /*
    $args = array(
        'table' => 'Persons',
        'where' => array(
            'City' => array(
                'value' => 'Citytowns',
                'sanitize' => 'text',
                'compare' => 'is'

            ),
            'FirstName' => array(
                'value' => 'Hank',
                'sanitize' => 'text',
                'compare' => 'not'
            )
            ),
            'order' => 'ASC',
            'orderBy' => 'id',
            'limit' => 10
    );
    */

    public function get($args){

        // Prevent the thing from running if the database is not working well
        if($this->db_works = false){ return; }

        // Set up a SQL string:
        $start = "SELECT * FROM ";
        $where = "";

        // 1. Table name
        $t['table'] = $this->sanitize($args['table'],'string');
        $start .= $t['table'];

        // 2. Where
        // Check if where is set
        if(!isset($args['where'])){ return; }

        // If set, update
        foreach($args['where'] as $key => $val){

            // sanitize the run and put it in an array
            $key          = $this->sanitize($key,'string');
            $t['w'][$key] = $this->sanitize($val['value'],$val['sanitize']);

            switch ($val['compare']) {
                case 'is':
                    $delimit = '=';
                    break;
                case 'not':
                    $delimit = '!=';
                    break;
                case 'largerthen':
                    $delimit = '>';
                    break;
                case 'smallerthen':
                    $delimit = '<';
                    break;
            }
            

            // Create the fields and values
            $where .= $key.$delimit."'".$t['w'][$key]."' AND ";

        }

        $where = " WHERE ".rtrim($where,' AND ');

        // ORDER
        if(isset($args['orderBy'])){

            if(!isset($args['order'])){ $o = "ASC"; }
            $t['orderBy'] = $this->sanitize($args['orderBy'],'string');
            $t['order'] = $this->sanitize($args['order'],'string');
            
            $order = " ORDER BY ".$t['orderBy']." ".$t['order']."";

        }else{ $order = ''; }

        // LIMIT
        if(isset($args['limit'])){

            if(!isset($args['order'])){ $o = "ASC"; }
            $t['limit'] = $this->sanitize($args['limit'],'string');
            
            $limit = " LIMIT ".$t['limit'];

        }else{ $limit = ''; }

        // . Assemble the SQL string and run:
        $sql = $start . $where . $order . $limit . ";";

        // Get the DB property
        $db = $this->db;

        // Run the SQL within the property
        $res = $db->query($sql);
        $this->sql_ran[] = $sql;

        // Return the output
        $list = $res->fetch_all(MYSQLI_ASSOC);

        return $list;

    }

    //////////////////////////////////////////

    // Count things from the database using an array of arguments:
    /*
    $args = array(
        'table' => 'Persons',
        'where' => array(
            'City' => array(
                'value' => 'Citytowns',
                'sanitize' => 'text',
                'compare' => 'is'

            ),
            'FirstName' => array(
                'value' => 'Hank',
                'sanitize' => 'text',
                'compare' => 'not'
            )
            ),
            'order' => 'ASC',
            'orderBy' => 'id'
        );
    */
    public function count($args){

        // Prevent the thing from running if the database is not working well
        if($this->db_works = false){ return; }

        $results = $this->get($args);
        if(!is_array($results)){ return false; }
        return count($results);

    }

    //////////////////////////////////////////

    // Run a custom SQL string
    public function sql($sql){

        // Prevent the thing from running if the database is not working well
        if($this->db_works = false){ return; }

        // Get the DB property
        $db = $this->db;

        // Run the SQL within the property
        $db->query($sql);

        // If needed, throuw out some error messages
        $this->db_error = $db->error_list;   
        
        // Add the query to the list of ran queries in this instance
        $this->sql_ran[] = $sql;

    

    }

    // Spit out an array of error messages:
    public function error(){

        return $this->db_error;

    }

    // A simple sanitize solution    
    public function sanitize($var, $type)
    {
        $flags = NULL;
        switch($type)
        {
            case 'url':
                $filter = FILTER_SANITIZE_URL;
            break;
            case 'int':
                $filter = FILTER_SANITIZE_NUMBER_INT;
            break;
            case 'float':
                $filter = FILTER_SANITIZE_NUMBER_FLOAT;
                $flags = FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND;
            break;
            case 'email':
                $var = substr($var, 0, 254);
                $filter = FILTER_SANITIZE_EMAIL;
            break;
            case 'string':
            default:
                $filter = FILTER_SANITIZE_STRING;
                $flags = FILTER_FLAG_NO_ENCODE_QUOTES;
            break;

        }
        $output = filter_var($var, $filter, $flags);        
        return($output);

    }

}
