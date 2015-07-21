<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	//default autoloader 
/*	 protected function _initAutoloader()  
     {  
         $autoloader = new Zend_Application_Module_Autoloader(array(  
             'namespace' => 'Application',  
             'basePath' => APPLICATION_PATH,  
         ));     
         return $autoloader;  
     } */
	
	/**
	 * 'default' module autoloader
	 * @return Zend_Application_Module_Autoloader $autoloader
	 */
	protected function  _initAutoloader()
	{		
		  $autoloader = new Zend_Application_Module_Autoloader(array(  
             'namespace' => '',  
             'basePath' => APPLICATION_PATH.'/default',  
         ));     
         
/*		if (!defined('ZVersion') || ZVersion!='1.10') {
			exit();
		}*/        
         return $autoloader;          
	}
	
	protected function _initCustomAutoloader() 
	{
		//  http://inchoo.net/zend/simple-controller-plugin-in-zend-framework/
		$autoloader = Zend_Loader_Autoloader::getInstance ();
		$autoloader->registerNamespace ( 'Lightwalker_' );
		$autoloader->suppressNotFoundWarnings(true); 		
	}
	
	
	protected function   _initSessions()
	 {
	     Zend_Session::setOptions(array('cookie_lifetime' =>172800 ));
	    // $this->bootstrap('session');
	    // Zend_Session::rememberMe();
	    // Zend_Session::start();    
	    
	 } 
	 
	// bootstrap db here so we can use db profiler  in  controller plugin 
	protected  function _initDatabase()
	{
	     $this->bootstrap("db");
	}
	 
	
	
	/**
	 *why we have to use router ,it's just so easy !
	 */
	protected function _initUrl()
	{			
		if ($_SERVER["SERVER_SOFTWARE"]=='Microsoft-IIS/7.0') {
			$_SERVER['HTTP_X_REWRITE_URL']=str_replace('.html','',$_SERVER['HTTP_X_REWRITE_URL']); // IIS
		}
		else {
			$_SERVER['REDIRECT_URL']=str_replace('.html','',$_SERVER['REDIRECT_URL']);  //Apache 
			$_SERVER['REQUEST_URI']=str_replace('.html','',$_SERVER['REQUEST_URI']);  //Apache	
		}
	}
     

	
	//http://blog.vandenbos.org/2009/07/19/zend-framework-module-specific-layout/
/*	protected function _intMvc()
	{		
	  	Zend_Layout::startMvc();
	}*/

     
	protected function _initViewLayout()
	{
		$this->bootstrap('layout');
		$layout = $this->getResource('layout');
		$view = $layout->getView();
		
		//helper 
		//registry view helper path , cuz the default view helper path link to   "views\helpers/ " not "/default/views/helpers/"
		$view->addHelperPath(APPLICATION_PATH.'/default/views/helpers/');
		
		// set some sitewide SEO info ;
		$view->headMeta()->appendName('keywords', '美国旅游,加拿大旅游,达美,达美集团，北京达美旅行社');
		$view->headMeta()->appendName('description', '达美网是美国达美集团旗下的旅游电子商务平台，达美提供美国和加拿大旅游服务及线路报价，包括境外境内参团旅游线路、主题旅游、酒店、票务、交通预订，以及签证服务、旅游个性订制、旅游资讯等。');
		$view->headMeta()->appendName('author', '');
		$view->headTitle('达美网 | 您的北美旅行专家 | 美国旅游和加拿大旅游线路预订','APPEND');
		$view->headTitle()->setSeparator(' | ');		
	}
	
	
	
	protected function _initLog()
	{
	   if ($this->hasPluginResource("log")) {
	       $log =  $this->getPluginResource("log")->getlog();
	       Zend_Registry::set("log", $log);
	       
	   }
	}
	

	protected function _initTranslate()
	{
	   // $locale = new Zend_Locale(Zend_Locale::BROWSER);
		// $locale->setLocale('en_US');
		// $locale->setDefault('en_US');		
		// Zend_Registry::set('Zend_Locale', $locale);
	
		/*$translate = new Zend_Translate ( 'array', APPLICATION_PATH . '/language/en_US.php', $locale);
		$translate->addTranslation(APPLICATION_PATH . '/language/zh_CN.php', 'zh');		
		$translate->setLocale('en_US');// this vaule could come from cookies if required 	*/

		//log not found messeges
	//	$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH.'/../log/translate/untranslated'.date('y-m-d').'.log');
	//	$log    = new Zend_Log($writer);
		
		$translate = new Zend_Translate(
		        array(
                    'adapter' => 'array',
                    'content' =>APPLICATION_PATH . '/language/en/dmw.php',
                    'locale'  => 'en',
		            'route' =>array("en"=>"zh"), // 
		       //     'log' =>$log
		        )
		    ); 
		    
		      $translate->addTranslation( ///add  the english language 
            array( 'content' =>APPLICATION_PATH . '/language/zh/dmw.php',
                 'locale'  => 'zh',
            )
        );    

		      $translate->addTranslation( // backend language file
                     array('content' =>APPLICATION_PATH . '/language/en/backend.php',
                     'locale'  => 'en',
                     // 'clear'   => true
                )
              );
              
         $translate->addTranslation( // backend language file
                     array('content' =>APPLICATION_PATH . '/language/zh/backend.php',
                     'locale'  => 'zh',
                     // 'clear'   => true
                )
              );
		 
		Zend_Registry::set ( 'Zend_Translate', $translate );		
	}
	
	protected function _initPlugin()
	{
	     // $this->bootstrap('view');
         //$view = $this->getResource('view');
	    $this->bootstrap('layout');
		$layout = $this->getResource('layout');
		$view = $layout->getView();
		
		Zend_Registry::set('View', $view);
	    
	    $front= Zend_Controller_Front::getInstance();
		
		$front->registerPlugin(new Lightwalker_Controller_Plugin_DetectLanguage($view));		
		
		$front->registerPlugin(new  Lightwalker_Controller_Plugin_SiteWideData());
		
		$front->registerPlugin(new Lightwalker_Controller_Plugin_Acl());
		
	     $front->registerPlugin(new Lightwalker_Controller_Plugin_PerformanceMonitor($view));
	}
	
	
	protected function _initActionHelpers() 
	{
		Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH.'/actionhelpers');
	}
	
	//http://zendframework.com/manual/en/zend.controller.router.html
	protected function _initRouter()
	{
		 $router=Zend_Controller_Front::getInstance()->getRouter();	
		 
       /*  $compat = new Zend_Controller_Router_Route_Module(array(),   $dispatcher,  $request);
         $router->addRoute('default', $compat);		 */ 
		  
		/* Note: 倒序匹配
		  用倒序来匹配路由确保最通用的路由被首先定义
		  Note: Reverse Matching
		  Routes are matched in reverse order so make sure your most generic routes are defined first*/
		  //这个是通用的路由（admin 模块等没指定路由的地方都用到），所以放前面
		 /* $router->addRoute('default',  new Zend_Controller_Router_Route(':@module/:@controller/:@action/*', //" /* " 确保URL传的参数有效
																						array('module'=>'default','controller'=>'index','action'=>'index')
																					));*/
		
		/*$route= new Zend_Controller_Router_Route(':controller/:action/*',
																						array('module'=>'default','controller'=>'index', 'action'=>'index')
																					);
		$router->addRoute('standard',$route);		*/
		
		
		$router->addRoute('archive',new Zend_Controller_Router_Route('archive/:url/*',
																						array('module'=>'default','controller'=>'news', 'action'=>'content','url'=>'')
																					));		
																					
																					
		 $router->addRoute('ct',new Zend_Controller_Router_Route('city/:city/:act/*',
																						array('module'=>'default','controller'=>'destination', 'action'=>'city','city'=>'','act'=>'')
																					));	   																
																					

		 		//universities 
	/*	$router->addRoute('uni', new Zend_Controller_Router_Route(':language/top-universities/:url/*', 
																						array('module'=>'default', 'controller' =>'universities', 'action' =>'index','url'=>'none' )));
		*/
		
		
		/*$route= new Zend_Controller_Router_Route('contact/:action/*',
																						array('module'=>'default','controller'=>'contact', 'action'=>'index')
																					);
		$router->addRoute('contact',$route);		
		
		$route= new Zend_Controller_Router_Route('about/:action/*',
																						array('module'=>'default','controller'=>'about', 'action'=>'index')
																					);
		$router->addRoute('about',$route);		
		
		$route= new Zend_Controller_Router_Route('expertise/:action/*',
																						array('module'=>'default','controller'=>'expertise', 'action'=>'index')
																					);
		$router->addRoute('expertise',$route);		
		*/
        
		//back -end user etc 
		$router->addRoute('admin', new Zend_Controller_Router_Route('dmyes/:controller/:action/*', 
																						array('module'=>'admin' , 'controller' =>'index' , 'action' =>'index')));
																						
																						
																
																						
	}
}

