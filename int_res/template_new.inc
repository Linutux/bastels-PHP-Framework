<?php
class template_new extends basement{
	public $formInput = Array();
	private $ultimateRuntimer = 1;
	private $headline = "";
	private $outputHtml = "";

	public function setFormInput($desc, $type, $param, $value){
		if(empty($param) || empty($type) || !is_string($param) || !is_string($type)){
			return FALSE;
		}
		else{
			/*switch(strtolower($type)){
			case "text": $this->formInput["text"][] = $param; break;
			case "email": $this->formInput["email"][] = $param; break;
			case "password": $this->formInput["password"][] = $param; break;
			case "submit": $this->formInput["submit"][] = $param; break;
			case "url": $this->formInput["url"][] = $param; break;
			case "reset": $this->formInput["reset"][] = $param; break;
			return TRUE;
			}*/
			$this->formInput[$this->ultimateRuntimer]["type"] = $type;
			$this->formInput[$this->ultimateRuntimer]["desc"] = $desc;
			$this->formInput[$this->ultimateRuntimer]["param"] = $param;
			$this->formInput[$this->ultimateRuntimer]["value"] = $value;
			$this->ultimateRuntimer += 1;
			return TRUE;
		}
	}
	
	public function setHeadline($headline){
		if(empty($headline) || !is_string($headline)){
			return FALSE;
		}
		else{
			$this->headline = $headline;
			return TRUE;
		}
	}
	
	public function formCreate(){
		$this->outputHtml = "<fieldset>\n";
		$this->outputHtml .= "	<legend>\n";
		$this->outputHtml .= $this->headline."\n";
		$this->outputHtml .= "</legend>\n";
		$this->outputHtml .= "<form id='form' method='post'>\n";
		/*foreach($this->formInput as $types => $typesArray){
			foreach($typesArray as $key => $value){
				if($types == "reset" || $types == "submit"){
					$this->outputHtml .= "<label for='".$value."'>".$value.": </label><input type='".$types."' name='".$value."' id='".$value."' value='".$value."' /><br>\n";
					break;
				}
				$this->outputHtml .= "<label for='".$value."'>".$value.": </label><input type='".$types."' name='".$value."' id='".$value."' value='' /><br>\n";	
			}
			//$this->outputHtml .= "<label for='".$value."'>".$value.": </label><input type='".$types."' name='".$value."' id='".$value."' value='' /><br>\n";	
		}*/
		foreach($this->formInput as $value){
			if($value["type"] == "reset"  || $value["type"] == "submit"){
				$this->outputHtml .= "<label for='".$value["param"]."'>".$value["desc"].": </label><input type='".$value["type"]."' name='".$value["param"]."' id='".$value["param"]."' value='".$value["desc"]."' /><br>\n";	
			}
			else{
				$this->outputHtml .= "<label for='".$value["param"]."' style='float:left'>".$value["desc"].": </label><input type='".$value["type"]."' name='".$value["param"]."' id='".$value["param"]."' value='".$value["value"]."' /><br>\n";	
			}
		}
		$this->outputHtml .= "</form>\n";
		$this->outputHtml .= "</fieldset>\n";
		return TRUE;
	}
	public function getOutputHtml(){
		return $this->outputHtml;
	}
}
?>