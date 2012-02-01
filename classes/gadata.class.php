<?php
class Gadata {
	private $_accesstoken;
	public $errors = array();
	
	function __construct($accesstoken){
		$this->_accesstoken = $accesstoken;	
	}
	//calls api and gets the data
	function callApi($url){
		
		$curl = curl_init($url);
	 
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);	
		$curlheader[0] = "Authorization: Bearer " . $this->_accesstoken;
		curl_setopt($curl, CURLOPT_HTTPHEADER, $curlheader);
		
	    //successful response will be json data
		$curl_response = curl_exec($curl);
		
		//catch curl error
        if($curl_response === false){
        	$json_error = '{"error":' . curl_error($curl_response) . '}';
        	$responseObj = json_decode($json_error);
        }else{
        	$responseObj = json_decode($curl_response);
        }
        
		curl_close($curl);
		
		return $responseObj;
	}
	
	//returns profile list as array	
	function parseProfileList(){
		$i = 0;
		$profiles = array();
		$profilesUrl = "https://www.googleapis.com/analytics/v3/management/accounts/~all/webproperties/~all/profiles";
			
		$profilesObj = $this->callApi($profilesUrl);
		
		//handle error in api request
		if(isset($profilesObj->error)){
			$profiles[0]["error"] = "Gadata->parseProfileList: ".$profilesObj->error;
		}
		else
		{
			foreach($profilesObj->items as $profile)
				{
					$profiles[$i] = array();
					$profiles[$i]["name"] = $profile->name;
					$profiles[$i]["profileid"] = $profile->id;
					$i++;
				}
		}

		return $profiles;	
	}
	
	//returns data as array	
	function parseData($requestUrl,$startdate,$enddate){
		$r = 0;
		$results = array();
		$requestUrl .= "&start-date=" . date("Y-m-d",strtotime($startdate)) . "&end-date=" . date("Y-m-d",strtotime($enddate));
		$dataObj = $this->callApi($requestUrl);
		
		//handle error in api request
		if(isset($dataObj->error)){
			$results[0]["error"] = "Gadata->parseData: ".$dataObj->error;
		}
		else
		{
			foreach($dataObj->rows as $row)
			{
				$results[$r] = array();
				$h = 0;
				foreach($dataObj->columnHeaders as $columnHeader)
				{
					//rewrite to strip after :
					$results[$r][ltrim($columnHeader->name,"ga:")] = $row[$h];
					$h++;
				}
				$r++;
			}
		}
		
		return $results;
	}
}
?>