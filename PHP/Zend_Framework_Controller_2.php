<?php
error_reporting(1);
require_once 'spreadsheet/excel_reader2.php';
require_once 'PHPExcel/IOFactory.php';


class Admin_ApianalyticsController extends Zend_Controller_Action
{
    public $uploadPath = null;
    public $completedFilesPath = null;
    public $newFilesPath = null;
    
    public function init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->setLayout(Csg_Model_User::getLayout());
        $this->view->headScript()->appendFile('/version3/js/apianalyticshelper.js?version=1');
        $this->view->headScript()->appendFile('/version3/js/dropzone.js');
        $this->view->headScript()->appendFile('/version3/jqgrid/js/i18n/grid.locale-en.js');
        $this->view->headScript()->appendFile('/version3/jqgrid/js/jquery.jqGrid.min.js');
        
        set_time_limit(1200);
        ini_set('max_execution_time',1200);
        ini_set('memory_limit', '-1');
        
        $options = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini', APPLICATION_ENV);
        $this->uploadPath = $options->photoUploadPath;
        $this->completedFilesPath = $this->uploadPath.'/api_analytics/completed_files/';
        $this->newFilesPath = $this->uploadPath.'/api_analytics/new_files/';
    }
    
    public function indexAction()
    {
        //grid data
        require_once 'grid/jqgrid_dist.php';
        
        //db config
        $options = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini', APPLICATION_ENV);
        $host    = $options->resources->db->params->host;
        $user    = $options->resources->db->params->username;
        $pwd     = $options->resources->db->params->password;
        $dbName  = $options->resources->db->params->dbname;
         
        // set up DB
        $conn = mysql_connect($host, $user, $pwd);
        mysql_select_db($dbName);
         
        // set your db encoding -- for ascent chars (if required)
        mysql_query("SET NAMES 'utf8'");
         
        // include and create object
        $g = new jqgrid();
         
        $col = array();
        $col["title"] = "id"; // caption of column
        $col["name"] = "id"; // grid column name, must be exactly same as returned column-name from sql (tablefield or field-alias)
        $col["editable"] = false;
        $col["hidden"] = true;
        $cols[] = $col;
         
        // grid config
        $col = array();
        $col["title"] = "Uploaded File"; // caption of column
        $col["name"] = "file_name"; // grid column name, must be exactly same as returned column-name from sql (tablefield or field-alias)
        $col["width"] = "12";
        $col["formatter"] = "function(cellval,options,rowdata){ return '<a target=\"_blank\" href=\"/uploads/api_analytics/new_files/'+cellval+'\">'+cellval+'</a>'; }";
        $col["editable"] = false;
        $col["hidden"] = false;
        $cols[] = $col;
         
        $col = array();
        $col["title"] = "Uploaded By"; // caption of column
        $col["name"] = "name"; // grid column name, must be exactly same as returned column-name from sql (tablefield or field-alias)
        $col["width"] = "8";
        $col["editable"] = false;
        $col["hidden"] = false;
        $cols[] = $col;
         
        $col = array();
		$col["title"] = "Upload Date"; // caption of column
		$col["name"] = "upload_date"; // grid column name, must be exactly same as returned column-name from sql (tablefield or field-alias)
		$col["width"] = "10";
		$col["align"] = "center";
		$col['editable'] = false;
		$col["formatter"] = "date";
		$col["formatoptions"] = array("srcformat"=>'Y-m-d H:i:s',"newformat"=>'m/d/y');
		$cols[] = $col;
         
        $col = array();
        $col["title"] = "Status"; // caption of column
        $col["name"] = "status"; // grid column name, must be exactly same as returned column-name from sql (tablefield or field-alias)
        $col["width"] = "8";
        $col["align"] = "center";
        $col["editable"] = true;
        $cols[] = $col;
        
        $col = array();
        $col["title"] = "Output File"; // caption of column
        $col["name"] = "output_file_name"; // grid column name, must be exactly same as returned column-name from sql (tablefield or field-alias)
        $col["width"] = "12";
        $col["formatter"] = "function(cellval,options,rowdata){ return '<a target=\"_blank\" href=\"/uploads/api_analytics/completed_files/'+cellval+'\">'+cellval+'</a>'; }";
        $col["editable"] = true;
        $cols[] = $col;
         
        // set few params
        $grid["caption"] = "Uploaded File Status";
        $grid["sortname"] = 'upload_date'; // by default sort grid by this field
        $grid["sortorder"] = "DESC"; // ASC or DESC
        $grid["rowNum"] = 25;
        //$grid["rowList"] = array(25,50,100);
        $grid["autowidth"] = true; // expand grid to screen width
        $grid["multiselect"] = false; // allow you to multi-select through checkboxes
        $grid["hidegrid"] = false;
        $grid["height"] = "500";
        $grid["loadtext"]= "Loading";
        $grid["form"]["position"] = "center";
        $grid["form"]["nav"] = false;
        $grid["forceFit"] = true;
        $grid["shrinkToFit"] = true;
        //$grid["export"] = array("format"=>"csv", "filename"=>"Buyer_List");
         
        $g->set_options($grid);
         
        $g->set_actions(array(
            "add"=>false, // allow/disallow add
            "edit"=>false, // allow/disallow edit
            "delete"=>false, // allow/disallow delete
            "rowactions"=>false, // show/hide row wise edit/del/save option
            "search" => "advance", // show single/multi field search condition (e.g. simple or advance)
            "autofilter" => true,
            "export_csv"=>false, // show/hide export to excel option
        )
        );
         
        $sql = "SELECT aaf.*, concat(u.firstName,' ', u.lastName) as name 
FROM api_analytics_files aaf
join user u on aaf.uploaded_by = u.userId";
         
        $g->select_command = $sql;
         
        // set database table for CRUD operations
        $g->table = 'api_analytics_files';
         
        // pass the cooked columns to grid
        $g->set_columns($cols);
         
        // render grid
        $this->view->out = $g->render("list1");
    }
    
    public function uploaditAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $timestamp   = time();
        $ds          = DIRECTORY_SEPARATOR;
        $userDetails = Csg_Model_User::getCurrentUserDetails();
         
        if (!empty($_FILES)) {
            
            try {
                //copy the file to the correct directory
                $name = $_FILES['file']['name'];
                $tempFile = $_FILES['file']['tmp_name'];
                $targetPath = $this->newFilesPath;
                $targetFileName = $timestamp . '_' . $_FILES['file']['name'];
                $targetFile =  $targetPath . $targetFileName;
                $result = move_uploaded_file($tempFile,$targetFile);
                if (!$result) {
                    error_log("ERROR: Could not move $tempFile to $targetFile", 0);
                    header('Content-Type: application/json');
                    echo json_encode('Unable to copy file on server. Check folder permissions.');
                    header("HTTP/1.1 404 Not Found");exit;
                }
                
                //store the file in the database.
                $data = array('file_name' => $targetFileName,
                    'uploaded_by' => $userDetails['userId'],
                    'upload_date' => date('Y-m-d H:i:s'),
                    'status' => 'New'
                );
                $aafModel = new Csg_Model_ApiAnalyticsFiles();
                if (!$aafModel->addNewFile($data)) {
                    header('Content-Type: application/json');
                    echo json_encode('Unable to add new file data to database. Please try again.');
                    header("HTTP/1.1 404 Not Found");exit;
                }
            } catch (Exception $e) {
                error_log($e->getMessage(), 0);
                header('Content-Type: application/json');
                echo json_encode($e->getMessage());
                header("HTTP/1.1 404 Not Found");exit;
            }
        }
    
        return true;
    }
    
}