# bPack Controller Plugin Auth

這個 Plugin 是用於對使用者或存取進行認證管理機制時使用的

您可以自由的繼承它，然後依照下面的說明進行設定

## Plugin Auth 運作架構

1. 使用者從 Controller 加入 Plugin Auth 本身或繼承的子物件 
2. Plugin 引擎 (stored in bPack/model/Event/Plugin.php) 會觸發二個動作，一個是 pluginInitialization 一個是 registerFunction

3. 註冊後，使用者可以依照您 Register 的方法名稱觸發您的 Function

## `user_login($username, $password)`

這裡會使用 Plugin Auth 在一初始化時所放入的 Auth Factor 進行檢查，如果驗證通過，即設定相關的 Session 變數，然後回傳 true 讓使用者可以在 controller 自定義如何動作

## 自定義 Plugin Auth

若您想要繼承 Plguin Auth，然後進行變更，您可以改寫以下方法：

* `pluginInitialization()` -> Plugin 啟動的方法，後面會有範例
* `registerFunctions()` -> 向 Controller 註冊 Plugin Method 的方法(建議不用更動)
* `setupLoggedSession()` -> 註冊相關 Session 變數的方法，如果您覺得內建之方法不夠方便，您可以自已寫一個方法
* `customLogonTemplate_Initialzation()` -> 如果您有自定義範本，要怎麼顯示(如放入網站名稱等)變數的方法

## 範例
    <?php
        class AuthExample extends Plugin_Auth
        {
            protected function pluginInitialization()
            {
                $this->session = new bPack_Session;

                $this->session_name = 'example';

                $this->setAuthenticationFactor(new AuthExample_Factor($this->parent->db));

                $this->custom_template = 'Plugin/ExampleAuth/'; // relative to tpl/

                $this->custom_login_action = array('admin','user','login'); // custom route for your login controller action
            }

            protected function customLogonTemplate_Initialzation($view)
            {
                $view->setOutputHandler(new bPack_View_Twig(bPack_Application_Directory . 'tpl/' . $this->custom_template . '/login.html'));

                return true;
            }
        }

        class AuthExample_Factor implements Plugin_Auth_Factor
        {
            public function __construct($db)
            {
                $this->db = $db;
            }

            public function isAuthenticated($username, $password) 
            {
                $data = $this->db->query("SELECT `id` FROM `username` = '$username' AND `password` = '$password' LIMIT 1;")->rowCount();
                
                if($data > 0)
                {
                    # means record exists, login success
                    return true;
                }
                else
                {
                    # no record, failed
                    retrun false;
                }

            }
        }

