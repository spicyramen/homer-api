<?php

/*
 * HOMER API Engine
 *
 * Copyright (C) 2011-2015 Alexandr Dubovikov <alexandr.dubovikov@gmail.com>
 * Copyright (C) 2011-2015 Lorenzo Mangani <lorenzo.mangani@gmail.com> QXIP B.V.
 *
 * The Initial Developers of the Original Code are
 *
 * Alexandr Dubovikov <alexandr.dubovikov@gmail.com>
 * Lorenzo Mangani <lorenzo.mangani@gmail.com> QXIP B.V.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
*/

namespace RestApi;

class Alarm {
    
    protected $_instance = array();
   
    private $authmodule = true;

    function __construct()
    {

    }

    /**
    * Checks if a user is logged in.
    *
    * @return boolean
    */
    public function getLoggedIn(){

	$answer = array();

	if($this->authmodule == false) return $answer;

        if(!$this->getContainer('auth')->checkSession())
	{
		$answer['sid'] = session_id();
                $answer['auth'] = 'false';
                $answer['status'] = 403;
                $answer['message'] = 'bad session';
                $answer['data'] = array();
	}

	return $answer;
    }
    
    public function getAlarmConfig($raw_get_data){
    
	/* auth */
        if(count(($adata = $this->getLoggedIn()))) return $adata;                

        /* get our DB */
        $db = $this->getContainer('db');
        $db->select_db(DB_STATISTIC);
        $db->dbconnect();
                         
        $data = array();
        $search = array();
        $callwhere = array();
        $calldata = array();
        $arrwhere = "";
        
        $table = "alarm_config";            
        $query = "SELECT * FROM ".$table." order by id DESC";
        $data = $db->loadObjectArray($query);

        /* sorting */
        //usort($data, create_function('$a, $b', 'return $a["micro_ts"] > $b["micro_ts"] ? 1 : -1;'));
                           
        $answer = array();          
                
        if(empty($data)) {
        
                $answer['sid'] = session_id();
                $answer['auth'] = 'true';             
                $answer['status'] = 200;                
                $answer['message'] = 'no data';                             
                $answer['data'] = $data;
                $answer['count'] = count($data);
        }                
        else {
                $answer['status'] = 200;
                $answer['sid'] = session_id();
                $answer['auth'] = 'true';             
                $answer['message'] = 'ok';                             
                $answer['data'] = $data;
                $answer['count'] = count($data);
        }
        
        return $answer;
    }


    public function doNewAlarmConfig($param){
    
	/* auth */
        if(count(($adata = $this->getLoggedIn()))) return $adata;                

        /* get our DB */
        $db = $this->getContainer('db');
        $db->select_db(DB_STATISTIC);
        $db->dbconnect();
                         
        $data = array();

        $search = array();
        $callwhere = array();
        $calldata = array();
        $arrwhere = "";
        
        $update['active'] = getVar('active', true, $param, 'bool');
        $update['name'] = getVar('name', '', $param, 'string');
        $update['email'] = getVar('email', '', $param, 'string');
        $update['type'] = getVar('type', '', $param, 'string');
        $update['value'] = getVar('value', 0, $param, 'string');        
        $update['startdate'] = getVar('startdate', '', $param, 'date');        
        $update['stopdate'] = getVar('stopdate', '', $param, 'date');        
          
        $exten = "";
        $callwhere = generateWhere($update, 1, $db, 0);
        if(count($callwhere)) {
                $exten .= implode(", ", $callwhere);                
        }
                              
        $table = "alarm_config";            
        $query = "INSERT INTO ".$table." SET ".$exten;        
        $db->executeQuery($query);        
        
        $uid = $db->getLastId();             
        
        $answer = $this->getAlarmConfig("");
        
        return $answer;
    }


    public function doEditAlarmConfig($param){
    
	/* auth */
        if(count(($adata = $this->getLoggedIn()))) return $adata;                

        /* get our DB */
        $db = $this->getContainer('db');
        $db->select_db(DB_STATISTIC);
        $db->dbconnect();
                         
        $data = array();

        $search = array();
        $callwhere = array();
        $calldata = array();
        $arrwhere = "";
        
        
        $update['active'] = getVar('active', true, $param, 'bool');
        $update['name'] = getVar('name', '', $param, 'string');
        $update['email'] = getVar('email', '', $param, 'string');
        $update['type'] = getVar('type', '', $param, 'string');
        $update['value'] = getVar('value', 0, $param, 'string');        
        $update['startdate'] = getVar('startdate', '', $param, 'date');        
        $update['stopdate'] = getVar('stopdate', '', $param, 'date');        
                
        $id = getVar('id', 0, $param, 'int');            
          
        $exten = "";
        $callwhere = generateWhere($update, 1, $db, 0);
        if(count($callwhere)) {
                $exten .= implode(", ", $callwhere);                
        }
                              
        $table = "alarm_config";;            
        $query = "UPDATE ".$table." SET ".$exten. " WHERE id=".$id;        
        $db->executeQuery($query);        
        
        $answer = $this->getAlarmConfig("");        
        return $answer;
    }
    
    public function deleteAlarmConfig($id){
    
	/* auth */
        if(count(($adata = $this->getLoggedIn()))) return $adata;                

        /* get our DB */
        $db = $this->getContainer('db');
        $db->select_db(DB_STATISTIC);
        $db->dbconnect();
                         
        $data = array();

        $search = array();
        $callwhere = array();
        $calldata = array();
        $arrwhere = "";
        
        $query = "DELETE FROM alarm_config WHERE id=".$id;
        $db->executeQuery($query);

        $answer = $this->getAlarmConfig("");                
        return $answer;
    }


    public function getAlarmList($raw_get_data){
    
	/* auth */
        if(count(($adata = $this->getLoggedIn()))) return $adata;                

        /* get our DB */
        $db = $this->getContainer('db');
        $db->select_db(DB_STATISTIC);
        $db->dbconnect();
                         
        $data = array();
        $search = array();
        $callwhere = array();
        $calldata = array();
        $arrwhere = "";
        
        $table = "alarm_data";            
        $limit = " limit 100";
        $query = "SELECT * FROM ".$table." where status = 1 and create_date > NOW() - INTERVAL 2 DAY order by create_date DESC".$limit;
        $data = $db->loadObjectArray($query);

        /* sorting */
        //usort($data, create_function('$a, $b', 'return $a["micro_ts"] > $b["micro_ts"] ? 1 : -1;'));
                           
        $answer = array();          
                
        if(empty($data)) {
        
                $answer['sid'] = session_id();
                $answer['auth'] = 'true';             
                $answer['status'] = 200;                
                $answer['message'] = 'no data';                             
                $answer['data'] = $data;
                $answer['count'] = count($data);
        }                
        else {
                $answer['status'] = 200;
                $answer['sid'] = session_id();
                $answer['auth'] = 'true';             
                $answer['message'] = 'ok';                             
                $answer['data'] = $data;
                $answer['count'] = count($data);
        }
        
        return $answer;
    }
     
     
     public function doEditAlarmList($param){
    
	/* auth */
        if(count(($adata = $this->getLoggedIn()))) return $adata;                

        /* get our DB */
        $db = $this->getContainer('db');
        $db->select_db(DB_STATISTIC);
        $db->dbconnect();
                         
        $data = array();

        $id = getVar('id', 0, $param, 'int');            
        $status = getVar('status', 0, $param, 'int');            
          
        $table = "alarm_data";;            
        $query = "UPDATE ".$table." SET status=".$status. " WHERE id=".$id;        
        $db->executeQuery($query);        
        
        $answer['status'] = 200;
        $answer['sid'] = session_id();
        $answer['auth'] = 'true';             
        $answer['message'] = 'ok';                             
        $answer['data'] = $data;
        $answer['count'] = 0;
        
        return $answer;
    }   
    
    public function doAlarmMethod($timestamp, $param){
    
	/* auth */
        if(count(($adata = $this->getLoggedIn()))) return $adata;                

        /* get our DB */
        $db = $this->getContainer('db');
        $db->select_db(DB_STATISTIC);
        $db->dbconnect();
                         
        $data = array();

        $search = array();
        $callwhere = array();
        $calldata = array();
        $arrwhere = "";
        
        foreach($param['filter'] as $key=>$filter) {
        
            $search[$key]['method'] = getVar('method', NULL, $filter, 'string');
            $search[$key]['cseq'] = getVar('cseq', NULL, $filter, 'string');
            $search[$key]['auth'] = getVar('auth', -1, $filter, 'int');
            $search[$key]['totag'] = getVar('method', -1, $filter, 'int');        
            
            $callwhere = generateWhere($search[$key], 1, $db, 0);                                          
            if(count($callwhere)) $calldata[] = "(". implode(" AND ", $callwhere). ")";
        }
        
        if(count($calldata)) $arrwhere = " AND (". implode(" OR ", $calldata). ")";
                        
        $time['from'] = getVar('from', round((microtime(true) - 300) * 1000), $timestamp, 'long');
        $time['to'] = getVar('to', round(microtime(true) * 1000), $timestamp, 'long');
        $time['from_ts'] = intval($time['from']/1000);
        $time['to_ts'] = intval($time['to']/1000);        
        
        $and_or = getVar('orand', NULL, $param['filter'], 'string');        
        $limit = getVar('limit', 500, $param, 'int');
        $total = getVar('total', false, $param, 'bool');
        
        $order = "";

        if($total) {
           $fields = "id, UNIX_TIMESTAMP(`from_date`) as from_ts, UNIX_TIMESTAMP(`to_date`) as to_ts, method, REPLACE(REPLACE(auth, 0,'N'),1,'A') AS auth, cseq, COUNT(id) as cnt, SUM(total) as total";
           $order .= " GROUP BY method, cseq, auth, totag";       
        }
        else {
           $fields = "id, UNIX_TIMESTAMP(`from_date`) as from_ts, UNIX_TIMESTAMP(`to_date`) as to_ts, method, REPLACE(REPLACE(auth, 0,'N'),1,'A') AS auth, cseq, totag, total";                                            
        }

         $order .= " order by id DESC";
        
        $table = "stats_method";            
        $query = "SELECT ".$fields." FROM ".$table." WHERE (`to_date` BETWEEN FROM_UNIXTIME(".$time['from_ts'].") AND FROM_UNIXTIME(".$time['to_ts']."))";
        $query.= $arrwhere;
        $query.= $order;
        $data = $db->loadObjectArray($query);

        /* sorting */
        usort($data, create_function('$a, $b', 'return $a["micro_ts"] > $b["micro_ts"] ? 1 : -1;'));
                           
        $answer = array();          
                
        if(empty($data)) {
        
                $answer['sid'] = session_id();
                $answer['auth'] = 'true';             
                $answer['status'] = 200;                
                $answer['message'] = 'no data';                             
                $answer['data'] = $data;
                $answer['count'] = count($data);
        }                
        else {
                $answer['status'] = 200;
                $answer['sid'] = session_id();
                $answer['auth'] = 'true';             
                $answer['message'] = 'ok';                             
                $answer['data'] = $data;
                $answer['count'] = count($data);
        }
        
        return $answer;

        
        return $answer;
    }
    
    
    public function getAlarmMethod($raw_get_data){
    
        $timestamp = $raw_get_data['timestamp'];
        $param = $raw_get_data['param'];

        return doAlarmMethod($timestamp, $param);
    }
    
    /* api: statistic/data */
    
    public function doAlarmData($timestamp, $param){
    
	/* auth */
        if(count(($adata = $this->getLoggedIn()))) return $adata;                

        /* get our DB */
        $db = $this->getContainer('db');
        $db->select_db(DB_STATISTIC);
        $db->dbconnect();
                         
        $data = array();

        $search = array();
        $callwhere = array();
        $calldata = array();
        $arrwhere = "";
        
        foreach($param['filter'] as $key=>$filter) {
        
            $search[$key]['type'] = getVar('type', NULL, $filter, 'string');            
            $callwhere = generateWhere($search[$key], 1, $db, 0);                                          
            if(count($callwhere)) $calldata[] = "(". implode(" AND ", $callwhere). ")";
        }
        
        if(count($calldata)) $arrwhere = " AND (". implode(" OR ", $calldata). ")";
                        
        $time['from'] = getVar('from', round((microtime(true) - 300) * 1000), $timestamp, 'long');
        $time['to'] = getVar('to', round(microtime(true) * 1000), $timestamp, 'long');
        $time['from_ts'] = intval($time['from']/1000);
        $time['to_ts'] = intval($time['to']/1000);        
        
        $and_or = getVar('orand', NULL, $param['filter'], 'string');        
        $limit = getVar('limit', 500, $param, 'int');
        $total = getVar('total', false, $param, 'bool');
        
        $order = "";

        if($total) {
           $fields = "id, UNIX_TIMESTAMP(`from_date`) as from_ts, UNIX_TIMESTAMP(`to_date`) as to_ts, type, COUNT(id) as cnt, SUM(total) as total";
           $order .= " GROUP BY type";       
        }
        else {
           $fields = "id, UNIX_TIMESTAMP(`from_date`) as from_ts, UNIX_TIMESTAMP(`to_date`) as to_ts, type, total";                                            
        }

         $order .= " order by id DESC";
        
        $table = "stats_data";            
        $query = "SELECT ".$fields." FROM ".$table." WHERE (`to_date` BETWEEN FROM_UNIXTIME(".$time['from_ts'].") AND FROM_UNIXTIME(".$time['to_ts']."))";
        $query.= $arrwhere;
        $query.= $order;
        $data = $db->loadObjectArray($query);

        /* sorting */
        usort($data, create_function('$a, $b', 'return $a["micro_ts"] > $b["micro_ts"] ? 1 : -1;'));
                           
        $answer = array();          
                
        if(empty($data)) {
        
                $answer['sid'] = session_id();
                $answer['auth'] = 'true';             
                $answer['status'] = 200;                
                $answer['message'] = 'no data';                             
                $answer['data'] = $data;
                $answer['count'] = count($data);
        }                
        else {
                $answer['status'] = 200;
                $answer['sid'] = session_id();
                $answer['auth'] = 'true';             
                $answer['message'] = 'ok';                             
                $answer['data'] = $data;
                $answer['count'] = count($data);
        }
        
        return $answer;

        
        return $answer;
    }
    
    
    public function getAlarmData($raw_get_data){
    
        $timestamp = $raw_get_data['timestamp'];
        $param = $raw_get_data['param'];

        return doAlarmData($timestamp, $param);
    }

    /* api: statistic/ip */
    
    public function doAlarmIP($timestamp, $param){
    
	/* auth */
        if(count(($adata = $this->getLoggedIn()))) return $adata;                

        /* get our DB */
        $db = $this->getContainer('db');
        $db->select_db(DB_STATISTIC);
        $db->dbconnect();
                         
        $data = array();

        $search = array();
        $callwhere = array();
        $calldata = array();
        $arrwhere = "";
        
        foreach($param['filter'] as $key=>$filter) {
        
            $search[$key]['method'] = getVar('method', NULL, $filter, 'string');            
            $search[$key]['source_ip'] = getVar('source_ip', NULL, $filter, 'string');            
            $callwhere = generateWhere($search[$key], 1, $db, 0);                                          
            if(count($callwhere)) $calldata[] = "(". implode(" AND ", $callwhere). ")";
        }
        
        if(count($calldata)) $arrwhere = " AND (". implode(" OR ", $calldata). ")";
                        
        $time['from'] = getVar('from', round((microtime(true) - 300) * 1000), $timestamp, 'long');
        $time['to'] = getVar('to', round(microtime(true) * 1000), $timestamp, 'long');
        $time['from_ts'] = intval($time['from']/1000);
        $time['to_ts'] = intval($time['to']/1000);        
        
        $and_or = getVar('orand', NULL, $param['filter'], 'string');        
        $limit = getVar('limit', 500, $param, 'int');
        $total = getVar('total', false, $param, 'bool');
        
        $order = "";

        if($total) {
           $fields = "id, UNIX_TIMESTAMP(`from_date`) as from_ts, UNIX_TIMESTAMP(`to_date`) as to_ts, source_ip, method, COUNT(id) as cnt, SUM(total) as total";
           $order .= " GROUP BY source_ip";       
        }
        else {
           $fields = "id, UNIX_TIMESTAMP(`from_date`) as from_ts, UNIX_TIMESTAMP(`to_date`) as to_ts, source_ip, method, total";                                            
        }

        $order .= " order by id DESC";
        
        $table = "stats_ip";            
        $query = "SELECT ".$fields." FROM ".$table." WHERE (`to_date` BETWEEN FROM_UNIXTIME(".$time['from_ts'].") AND FROM_UNIXTIME(".$time['to_ts']."))";
        $query.= $arrwhere;
        $query.= $order;
        $data = $db->loadObjectArray($query);

        /* sorting */
        usort($data, create_function('$a, $b', 'return $a["micro_ts"] > $b["micro_ts"] ? 1 : -1;'));
                           
        $answer = array();          
                
        if(empty($data)) {
        
                $answer['sid'] = session_id();
                $answer['auth'] = 'true';             
                $answer['status'] = 200;                
                $answer['message'] = 'no data';                             
                $answer['data'] = $data;
                $answer['count'] = count($data);
        }                
        else {
                $answer['status'] = 200;
                $answer['sid'] = session_id();
                $answer['auth'] = 'true';             
                $answer['message'] = 'ok';                             
                $answer['data'] = $data;
                $answer['count'] = count($data);
        }
        
        return $answer;

        
        return $answer;
    }
    
    
    public function getAlarmIP($raw_get_data){
    
        $timestamp = $raw_get_data['timestamp'];
        $param = $raw_get_data['param'];

        return doAlarmIP($timestamp, $param);
    }
    

    /* api: statistic/uac */
    
    public function doAlarmUserAgent($timestamp, $param){
    
	/* auth */
        if(count(($adata = $this->getLoggedIn()))) return $adata;                

        /* get our DB */
        $db = $this->getContainer('db');
        $db->select_db(DB_STATISTIC);
        $db->dbconnect();
                         
        $data = array();

        $search = array();
        $callwhere = array();
        $calldata = array();
        $arrwhere = "";
        
        foreach($param['filter'] as $key=>$filter) {        
            $search[$key]['method'] = getVar('method', NULL, $filter, 'string');            
            $search[$key]['useragent'] = getVar('useragent', NULL, $filter, 'string');            
            $callwhere = generateWhere($search[$key], 1, $db, 0);                                          
            if(count($callwhere)) $calldata[] = "(". implode(" AND ", $callwhere). ")";
        }
        
        if(count($calldata)) $arrwhere = " AND (". implode(" OR ", $calldata). ")";
                        
        $time['from'] = getVar('from', round((microtime(true) - 300) * 1000), $timestamp, 'long');
        $time['to'] = getVar('to', round(microtime(true) * 1000), $timestamp, 'long');
        $time['from_ts'] = intval($time['from']/1000);
        $time['to_ts'] = intval($time['to']/1000);        
        
        $and_or = getVar('orand', NULL, $param['filter'], 'string');        
        $limit = getVar('limit', 500, $param, 'int');
        $total = getVar('total', false, $param, 'bool');
        
        $order = "";

        if($total) {
           $fields = "id, UNIX_TIMESTAMP(`from_date`) as from_ts, UNIX_TIMESTAMP(`to_date`) as to_ts, useragent, method, COUNT(id) as cnt, SUM(total) as total";
           $order .= " GROUP BY useragent";       
        }
        else {
           $fields = "id, UNIX_TIMESTAMP(`from_date`) as from_ts, UNIX_TIMESTAMP(`to_date`) as to_ts, useragent, method, total";                                            
        }

        $order .= " order by id DESC";
        
        $table = "stats_useragent";            
        $query = "SELECT ".$fields." FROM ".$table." WHERE (`to_date` BETWEEN FROM_UNIXTIME(".$time['from_ts'].") AND FROM_UNIXTIME(".$time['to_ts']."))";
        $query.= $arrwhere;
        $query.= $order;
        $data = $db->loadObjectArray($query);

        /* sorting */
        usort($data, create_function('$a, $b', 'return $a["micro_ts"] > $b["micro_ts"] ? 1 : -1;'));
                           
        $answer = array();          
                
        if(empty($data)) {
        
                $answer['sid'] = session_id();
                $answer['auth'] = 'true';             
                $answer['status'] = 200;                
                $answer['message'] = 'no data';                             
                $answer['data'] = $data;
                $answer['count'] = count($data);
        }                
        else {
                $answer['status'] = 200;
                $answer['sid'] = session_id();
                $answer['auth'] = 'true';             
                $answer['message'] = 'ok';                             
                $answer['data'] = $data;
                $answer['count'] = count($data);
        }
        
        return $answer;

        
        return $answer;
    }
    
    
    public function getAlarmUserAgent($raw_get_data){
    
        $timestamp = $raw_get_data['timestamp'];
        $param = $raw_get_data['param'];

        return doAlarmUserAgent($timestamp, $param);
    }
    

    
    public function getContainer($name)
    {
        if (!$this->_instance || !isset($this->_instance[$name]) || $this->_instance[$name] === null) {
            //$config = \Config::factory('configs/config.ini', APPLICATION_ENV, 'auth');
            if($name == "auth") $containerClass = sprintf("Authentication\\".AUTHENTICATION);
            else if($name == "db") $containerClass = sprintf("Database\\".DATABASE_CONNECTOR);            
            $this->_instance[$name] = new $containerClass();
        }
        return $this->_instance[$name];
    }

    /**
     * @param string $server
     * @url stats/([0-9]+)
     * @url stats
     * @return string
     */
    public function getStats($server = '1'){
        return $this->getServerStats($server);
    }
    
    
    public function pcapCheckSum($data) {

	if( strlen($data)%2 ) $data .= "\x00";
	$bit = unpack('n*', $data);
	$sum = array_sum($bit);
	while ($sum >> 16) $sum = ($sum >> 16) + ($sum & 0xffff);
	$sum = ~$sum;
	$sum = $sum & 0xffff;
	return $sum;    

    } 
    
}

?>
