<?php

class Plugin_Auth extends bPack_Event_Plugin
{
    protected $session;
    protected $factor = null;
    protected $session_name = 'bpack.plugin.auth';
    protected $custom_template = ''; 
    protected $custom_login_action = 'user.login/login';
    
    #
    # Plugin Initializtion settings
    # 
    protected function pluginInitialization()
    {
        $this->session = new bPack_Session;

        $this->setAuthenticationFactor(new Plugin_Auth_DummyFactor);
    }

    #
    # Register Plugin methods to Controller
    #
    # eg: (inside controller) $this->_user_isLogged();
    #
    protected function registerFunctions()
    {
        $this->parent
            ->registerPluginFunction('user_isLogged', array($this, 'user_logged_in'))
            ->registerPluginFunction('user_showLoginPage', array($this, 'user_show_login_page'))
            ->registerPluginFunction('user_login', array($this, 'user_login'))
            ->registerPluginFunction('user_logout', array($this, 'user_logout'))
            ->registerPluginFunction('user_getUsername',array($this, 'getUsername'))
            ->registerPluginFunction('user_getUserId',array($this, 'getUserId'));
    }

    #
    # Get the logged username
    #
    public function getUsername()
    {
        # If not logged, return false
        if(!$this->user_logged_in())
        {
            return false;
        }

        return $this->session->get($this->session_name)->username;
    }
    
    #
    # Get the logged user id
    #
    public function getUserId()
    {
        # If not logged, return false
        if(!$this->user_logged_in())
        {
            return false;
        }

        return $this->session->get($this->session_name)->id;
    }
    
    #
    # Setup sessions after login successfully
    #
    protected function setupLoggedSession($username)
    {
        $data = new bPack_DataContainer;

        $data->username = $username;
        $data->id = $this->factor->getUserId($username);

        $this->session->set($this->session_name , $data);

        return true;
    }
    
    #
    # Clean all data that stored by this plugin within session
    #
    protected function cleanSession()
    {
        $this->session->clear($this->session_name);

        return true;
    }

    #
    # Logout
    #
    public function user_logout()
    {
        # If logged
        if($this->user_logged_in())
        {
            # Clean all data stored
            $this->cleanSession();
            
            return true;
        }
        else
        {
            # We are not logged to logout 
            return false;
        }
    }

    #
    # Login
    #
    public function user_login($username, $password)
    {
        # If there's no authentication factor, we use DummyFactor
        if(is_null($this->factor))
        {
            $this->factor = new Plugin_Auth_DummyFactor;
        }
        
        # Pass the username and password to authentication factor, and check if passed.
        if($this->factor->isAuthenticated($username, $password))
        {
            # If passed, setup session
            $this->setupLoggedSession($username);

            return true;
        }

        return false;
    }

    #
    # Has user logged?
    #
    public function user_logged_in()
    {
        if($this->session->get($this->session_name) === FALSE)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    #
    # Display the login page
    #
    public function user_show_login_page()
    {
        $view = new bPack_View;
           
        $view->assign('login_url', $this->parent->response->get_internal_link($this->custom_login_action));

        if(file_exists(bPack_Application_Directory . 'tpl/' . $this->custom_template . 'login.html'))
        {
            $this->customLoginTemplate_Initialzation($view);
        }
        else
        {
            $view->setOutputHandler(new bPack_View_Twig(bPack_Application_Directory . 'lib/plugin/Auth/tpl/'));
        }
        
        $view->output('login.html');
        // end and await for next submit
        exit;
    }
    
    #
    # set the authentication factyor for plugin
    #
    public function setAuthenticationFactor(Plugin_Auth_Factor $factor_obj)
    {
        $this->factor = $factor_obj;

        return true;
    }
}

class Plugin_Auth_DummyFactor implements Plugin_Auth_Factor
{
    public function getUserId($username)
    {
        return 'Dummy';
    }

    public function isAuthenticated($username, $password)
    {
        if($username == '123' && $password == '123')
        {
            return true;
        }

        return false;
    }
}

interface Plugin_Auth_Factor
{
    public function isAuthenticated($username, $password);
    public function getUserId($username);
}
