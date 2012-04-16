<?php
// bashing everybody that trys to call this file directly 
$pathArray = explode("/", __FILE__);
if(!defined(strstr($pathArray[count($pathArray)-1], ".", true))){
	print_r($pathArray);
	echo "<br>wtf, wo ist das errorhandling?!<br>";
	// errorhandling can be placed here
	exit;
}

class templateEngine extends basement{

	##
	# This fucking B&Auml;M script parse every template file and puts content into it
	##
	## todo public function bockMod($blockname, tpl), erstellt einen block falls er noch nicht existiert, 
	## hauml;ngt tpl hinten drann wie addBlock+setBlockTemplate
	private $content = array(); 
	private $blocks = array();
	//private $blockTemplate = array();
	private $template = "";
	private $stylePresent = "generic";
	private $style = "";
	private $tplDir = "templates";
	private $ErrorMsg;
	//private $theUltimateCounterVar = 0;
	public 	$error123 = "";
		
	function  __construct($user){
		$this->getStyle($user);
		$this->setTemplate("head.tpl");
	}
	
	public function addBlock($blockName = ""){
		if(!empty($blockName)){
			$this->blocks[$blockName] = "";
		}
	}
	
	public function setBlockTemplate($blockName = "", $tpl = ""){
		$path = $this->tplDir."/".$this->style."/".$tpl;
		if(!file_exists($path)){
			$this->ErrorMsg .= "<span>Error: Style ".$this->style." doesn't contain the ".$tpl." file. path is ".$path."</span><br>\n";
			$path = $this->tplDir."/".$this->stylePresent."/".$tpl;
			if(!file_exists($path)){
				echo "you failed sooo bad...";
				exit;			
			}
		}	
		$this->blocks[$blockName] .= file_get_contents($path);
	}
	
	public function setBlockContent($blockName, $var, $content){
		$this->blocks[$blockName] = str_replace("{".$var."}", $content, $this->blocks[$blockName]);
	}
	
	public function addBlock2Content($blockName){
		if(array_key_exists($blockName, $this->blocks) && !array_key_exists($blockName, $this->content)){
			$this->content[$blockName] = $this->blocks[$blockName];
		}
	}
	
	/**
	 * setContent
	 *
	 * Saves any content and related vars for templates
	 *
	 * @param string $var			the related var
	 * @param string $content	the importent content
	 * 
	 * @return TRUE/FALSE			depends on what happends
	 */
	public function setContent($var = "", $content = ""){
		if(empty($var)){
			$error = "var is empty";
			return FALSE;
		}
		elseif(empty($content)){
			$error = "content is empty";
			return FALSE;
		}
		elseif(!is_string($var)){
			$error = "var is not a string";
			return FALSE;
		}
		elseif(!is_string($content)){
			$error = "content is not a string";
			return FALSE;			
		}
		elseif(array_key_exists($var, $this->content)){
			//$error = "key is twice, race condition :p"; thats not an error anymore :)
			//if(is_array($this->content) && is_string($var)){
			//echo "das ist ein array, var ist ein string";
			//$this->error123 .= $content;
			if(!is_array($this->content[$var])){
				$this->content[$var] = array($this->content[$var]);
				
				//$tmpContent = $this->content[$var];
				//unset($this->content[$var]);
				//$this->content[$var] = array($this->theUltimateCounterVar++ => $tmpContent);
				
			}
			//$this->error123 .= gettype($this->content[$var])." dasisteintest";
			$this->content[$var][] = $content;
			//$this->theUltimateCounterVar++;
			return TRUE;
			//}
		}
		else{
			$this->content[$var] = $content;
			return TRUE;
		}
	}
	

	
	/**
	* setTemplate
	*
	* loads templatefiles
	*
	* @param String $template template name
	*/
	public function setTemplate($template = ""){
		$path = $this->tplDir."/".$this->style."/".$template;
		if(!file_exists($path)){
			$this->ErrorMsg .= "<span>Error: Style ".$this->style." doesn't contain the ".$template." file. path is ".$path."</span><br>";
			$path = $this->tplDir."/".$this->stylePresent."/".$template;
			if(!file_exists($path)){
				echo "you failed sooo bad...";
				exit;			
			}
		}
		if(empty($this->template)){
			$this->template = file_get_contents($path);
		}
		else{
			$this->template .= file_get_contents($path);
		}
	}
	
	/**
	* parseTemplate
	*
	* replaces every var in the template with the equivalent content
	*/
	public function parseTemplate(){
		$this->setTemplate("footer.tpl");
		if(!array_key_exists("css", $this->content)){
			$this->setContent("css", " ");
		}
		if(!array_key_exists("js", $this->content)){
			$this->setContent("js", " ");
		}
		foreach($this->content as $key => $value){
			/* Start - is the content unique? */
			if(is_string($value)){
				$this->template = str_replace("{".$key."}", $value, $this->template);
			/* if not unique... */	
			}else{
				$tmp = "";
				
				foreach($value as $newValue){
					/* Write every ununique contentvalues into tmp */
					$tmp .= $newValue."\n";
					
				}
				/*$this->error123 .= $tmp."\n<br>";
				$this->error123 .= $newValue."\n<br>";
				$this->error123 .= gettype($value)."\n<br>";
				$this->error123 .= print_r($value, TRUE)."\n<br>";*/
				
				$this->template = str_replace("{".$key."}", $tmp, $this->template);
			}
			
		}
		//$this->error123 = "<pre>".print_r($this->content, true)."</pre>";
		//$this->error123 = "1234";
		//$tidy = tidy_parse_string($this->template);
		//$this->template = $tidy->html();
	}
	
	/**
	* getTemplate
	* 
	* @return String template		returns the template
	*/ 
	public function getTemplate(){
		return $this->template;
	}
	
	/**
	* showTemplate
	*
	* echo the template
	*/
	public function showTemplate(){
		echo $this->template;
	}
	
	private function getStyle($user){
		$array = $user->getUserInfo();
		if($array === ARRAY()){
			if(array_key_exists("style", $array)){
				if(is_dir("templates/".$array["style"])){
					$this->style = $array["style"];
				}
			}
			else{
				$this->style = $this->stylePresent;
			}
		}
		//$this->template .= "<pre>".print_r($array, true)."</pre>";
	}
	
	/*public function setDefaultCSS(){
	
	}
	
	public function setDefaultJS(){
	
	}*/
}