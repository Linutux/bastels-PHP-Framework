<?php 
 $leftColumn = "";
	$middleColumn = "";
	$rightColumn = "";
	
	$columns = array('leftColumn','middleColumn','rightColumn');
	foreach($columns as $column){
		//Start - Parse Columns (left, right)
			if($column == 'leftColumn' || $column == 'rightColumn'){
				//Start - Get boxes of the column
					$query = "SELECT `boxPlaceholder` FROM `".TABLE_BOXES."` WHERE `boxAlign` = '".substr($column,0,-6)."' ORDER BY `boxSort` ASC";
					$Sql->Query($query);
					while($row = $Sql->fetchAssoc()){
						//Get Template
						$tpl->setCurrentTemplate($row['boxPlaceholder'].'.tpl');
						// $tpl->loadTemplatefile($row['boxPlaceholder'].'.tpl', false, false);
						
						//Get Box
						if(file_exists($rootPath.'/includes/boxes/'.$row['boxPlaceholder'].'.php')){
							require($rootPath.'/includes/boxes/'.$row['boxPlaceholder'].'.php');
						}
						//Write template into columnvariable
						$$column .= $tpl->get();
					}
					$Sql->freeResult();
				//End - Get boxes of the column
			}
		//End - Parse Columns (left, right)
		
		//Start - Parse Columns (middle)
			else if($column == 'middleColumn'){
				//Get Content
				require($rootPath.$page.'.php');
				
				$$column .= $tpl->get();          
			}
		//End - Parse Columns (middle)
	}