<?php
class Template
  {
    private $_templateDirectory = "";
    private $_template = "";
    private $_blocks = array();
    
    public function __construct($templateDirectory)
    {
      $this->_templateDirectory = $templateDirectory;
    }
    
    public function setCurrentTemplate($template)
    {
      $this->_template = file_get_contents($this->_templateDirectory.'/'.$template);
    }
    
    public function setBlock($blockname,$blockfile)
    {
      if(empty($this->_blocks[$blockname]))
        $this->_blocks[$blockname] = file_get_contents($this->_templateDirectory.'/'.$blockfile);
      else
        $this->_blocks[$blockname] .= file_get_contents($this->_templateDirectory.'/'.$blockfile);
    }
    
    public function setVariable($placeholder,$value,$blockname = '')
    {
      if($blockname == '')
      {
        $this->_template = str_replace('{'.$placeholder.'}',$value,$this->_template);
      }
      else
      {
        $this->_blocks[$blockname] = str_replace('{'.$placeholder.'}',$value,$this->_blocks[$blockname]);
      }
    }
    
    public function parseBlock($placeholder,$blockname)
    {
      $this->_template = str_replace('{'.$placeholder.'}',$this->_blocks[$blockname],$this->_template);
    }
    
    public function get()
    {
      return $this->_template;
    }
    
    public function show()
    {
      echo $this->_template;
    }
  }