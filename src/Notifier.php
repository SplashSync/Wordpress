<?php

namespace Splash\Local;

use Splash\Core\SplashCore  as Splash;

class Notifier
{
    private static $instance;
    const NOTICE_FIELD = 'splash_admin_messages';

    protected function __construct() {}
    private function __clone() {}
    private function __wakeup() {}

    static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
    *   @abstract     Register Post & Pages, Product Hooks
    */
    static public function registeHooks()   {
        add_action( 'admin_notices',    [self::class, 'displayAdminNotice']);
    }  
    
    public function displayAdminNotice()
    {
        $option      = get_option(self::NOTICE_FIELD);
        $message     = isset($option['message']) ? $option['message'] : false;
        $noticeLevel = ! empty($option['notice-level']) ? $option['notice-level'] : 'notice-error';

        if ($message) {
            echo "<div class='notice {$noticeLevel} is-dismissible'><p>{$message}</p></div>";
            delete_option(self::NOTICE_FIELD);
        }
    }


    
    public function importLog()
    {
        
        $RawLog     =   Splash::Log()->GetRawLog();
        $Type       =   Null;
        $Contents   =   Null;
        
        //====================================================================//
        // Store Log - Debug        
        if (!empty($RawLog->deb)) {
            $Type       =   'notice-info'; 
            $Contents  .=  Splash::Log()->GetHtml($RawLog->deb);
        }        
        //====================================================================//
        // Store Log - Messages        
        if (!empty($RawLog->msg)) {
            $Type       =   'notice-success'; 
            $Contents  .=  Splash::Log()->GetHtml($RawLog->msg, Null, "#006600");
        }
        //====================================================================//
        // Store Log - Warnings        
        if (!empty($RawLog->war)) {
            $Type       =   'notice-warning'; 
            $Contents  .=  Splash::Log()->GetHtml($RawLog->war, Null, "#FF9933");
        }
        //====================================================================//
        // Store Log - Errors        
        if (!empty($RawLog->err)) {
            $Type       =   'notice-error'; 
            $Contents  .=  Splash::Log()->GetHtml($RawLog->err, Null, "#FF3300");
        }
        
        if (!empty($Type) && !empty($Contents)) {
            $this->updateOption($Contents, $Type);
        } 
    }
    
    public function displayError($message)
    {
        $this->updateOption($message, 'notice-error');
    }

    public function displayWarning($message)
    {
        $this->updateOption($message, 'notice-warning');
    }

    public function displayInfo($message)
    {
        $this->updateOption($message, 'notice-info');
    }

    public function displaySuccess($message)
    {
        $this->updateOption($message, 'notice-success');
    }

    protected function updateOption($message, $noticeLevel) {
        update_option(self::NOTICE_FIELD, [
            'message' => $message,
            'notice-level' => $noticeLevel
        ]);
    }
}

