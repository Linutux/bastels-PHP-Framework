<?php
class template extends basement{

	private $formParamHead = "";
	private $formParamEmail = FALSE;
	private $formParamUser = FALSE;
	private $formParamPass = FALSE;
	private $formHtml = "";

	public function formSet($formParamHead = "", $formParamEmail = FALSE, $formParamUser = FALSE, $formParamPass = FALSE){
		if(is_string($formParamHead) && !empty($formParamHead)){
		$this->formParamHead = $formParamHead;
		}
		if(is_bool($formParamEmail) && !empty($formParamEmail)){
		$this->formParamEmail = $formParamEmail;
		}
		if(is_bool($formParamUser) && !empty($formParamUser)){
		$this->formParamUser = $formParamUser;
		}
		if(is_bool($formParamPass) && !empty($formParamPass)){
		$this->formParamPass = $formParamPass;
		}
	}
	public function formCreate(){
		$this->formHtml = "<fieldset>\n";
		$this->formHtml .= "	<legend>\n";
		$this->formHtml .= $this->formParamHead."\n";
		$this->formHtml  .= "</legend>\n";
		$this->formHtml  .= "<form id='form' method='post'>\n";
		if($this->formParamUser){
			$this->formHtml  .= "	<label for='username'>Benutzername: </label><input type='text' name='username' id='username' value='' /><br />\n";
		}
		if($this->formParamUser){
			$this->formHtml  .= "	<label for='password'>Passwort: </label><input type='password' name='password' id='password' value='' /><br />\n";
		}
		if($this->formParamUser){
			$this->formHtml  .= "	<label for='email'>eMailadresse: </label><input type='email' name='email' id='email' value='' /><br />\n";
		}
		$this->formHtml  .= "	<input type='submit' name='login' id='login' value='Anmelden' />\n";
		$this->formHtml  .= "</form>\n";
		$this->formHtml  .= "</fieldset>\n";
		return $this->formHtml;
	}





}
?>