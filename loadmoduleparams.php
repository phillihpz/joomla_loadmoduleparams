<?php
/**
* @package pmod
* @version 1.0
* @copyright Copyright (C) 2013 Xelltech, Inc. All rights reserved.
* @license GPL
* @author Phillihp Harmon
* @website http://www.phillihp.com,  https://github.com/phillihpz/joomla_loadmoduleparams
*/

// No direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgContentLoadModuleParams extends JPlugin{	

	public function onContentPrepare($context, &$article, &$params, $limitstart) {		
		if ($context == 'com_finder.indexer') {
			return true;
		}
	  
		$regex = '/{(pmod)\s*(.*?)}/i';
		
		$matches = array();
		$preg_set_order = PREG_SET_ORDER;
		preg_match_all($regex, $article->text, $matches, $preg_set_order);  
		
		foreach ($matches as $match){
			$module = '';
			$arguments = array();   		
			preg_match_all('/\[.*?\]/', $match[2], $arguments);		
			if ($arguments) {
				foreach ($arguments as $i=>$argument){
					$module = preg_replace("/\[|]/", '', $argument);
				}
			}
			
			$paramsarray = explode('|', $module[0]);
			
			$module_name	= $paramsarray[0];
			$module_params	= Array();
			if(isset($paramsarray[1])){
				parse_str($paramsarray[1], $module_params);
			}			
			
			$module_output = $this->load_module($module_name, $module_params);
			$article->text = preg_replace($regex, $module_output, $article->text, 1);
		}
	}
	
	protected function load_module($module_name, $module_params) {
		
		$document = &JFactory::getDocument();
		$renderer = $document->loadRenderer('module');
		
		$contents = '';
		
		//get module as an object
		$database = JFactory::getDBO();
		$database->setQuery("SELECT * FROM #__modules WHERE module='$module_name'");
		$modules = $database->loadObjectList();
		$module = $modules[0];
		
		$module->user = '';
		
		$curParams = (array)json_decode($module->params);
		
		foreach($module_params as $key=>$item) {
			$curParams[$key] = $item;
		}
		$curParams['moduleinclude'] = true;
		$module->params = json_encode($curParams); 
		
		$contents = $renderer->render($module, $module_params);
		
		return $contents;
	}
}

?>