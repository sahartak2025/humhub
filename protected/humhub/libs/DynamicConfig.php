<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\models\Setting;
use Yii;

/**
 * Description of DynamicConfig
 *
 * @author luke
 */
class DynamicConfig extends \yii\base\Object
{

    public static function onSettingChange($setting)
    {
        // Only rewrite static configuration file when necessary
        if ($setting->module_id != 'mailing' &&
                $setting->module_id != 'cache' &&
                $setting->name != 'name' &&
                $setting->name != 'theme' &&
                $setting->name != 'authentication_internal'
        ) {
            return;
        }

        self::rewrite();
    }

    public static function load()
    {
        $configFile = self::getConfigFilePath();
        
        if (!is_file($configFile)) {
            self::save([]);
        }
        
        $config = require($configFile);

        if (!is_array($config))
            return array();

        return $config;
    }

    public static function save($config)
    {
        $content = "<" . "?php return ";
        $content .= var_export($config, true);
        $content .= "; ?" . ">";

        $configFile = self::getConfigFilePath();
        file_put_contents($configFile, $content);

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($configFile);
        }

        if (function_exists('apc_compile_file')) {
            apc_compile_file($configFile);
        }
    }

    public static function rewrite()
    {

        // Get Current Configuration
        $config = self::load();

        // Add Application Name to Configuration
        $config['name'] = Setting::Get('name');

        // Add Default language
        $defaultLanguage = Setting::Get('defaultLanguage');
        if ($defaultLanguage !== null && $defaultLanguage != "") {
            $config['language'] = Setting::Get('defaultLanguage');
        } else {
            $config['language'] = Yii::$app->language;
        }

        // Add Caching
        /*
        $cacheClass = Setting::Get('type', 'cache');
        if (!$cacheClass) {
            $cacheClass = "CDummyCache";
        }
        $config['components']['cache'] = array(
            'class' => $cacheClass,
        );
        */
        
        /*
        // Add User settings
        $config['components']['user'] = array();
        if (Setting::Get('defaultUserIdleTimeoutSec', 'authentication_internal')) {
            $config['components']['user']['authTimeout'] = Setting::Get('defaultUserIdleTimeoutSec', 'authentication_internal');
        }
        */
        /*
        // Install Mail Component
        $mail = array(
            'class' => 'ext.yii-mail.YiiMail',
            'transportType' => Setting::Get('transportType', 'mailing'),
            'viewPath' => 'application.views.mail',
            'logging' => true,
            'dryRun' => false,
        );
        if (Setting::Get('transportType', 'mailing') == 'smtp') {

            $mail['transportOptions'] = array();

            if (Setting::Get('hostname', 'mailing'))
                $mail['transportOptions']['host'] = Setting::Get('hostname', 'mailing');

            if (Setting::Get('username', 'mailing'))
                $mail['transportOptions']['username'] = Setting::Get('username', 'mailing');

            if (Setting::Get('password', 'mailing'))
                $mail['transportOptions']['password'] = Setting::Get('password', 'mailing');

            if (Setting::Get('encryption', 'mailing'))
                $mail['transportOptions']['encryption'] = Setting::Get('encryption', 'mailing');

            if (Setting::Get('port', 'mailing'))
                $mail['transportOptions']['port'] = Setting::Get('port', 'mailing');

            if (Setting::Get('allowSelfSignedCerts', 'mailing')) {
                $mail['transportOptions']['options']['ssl']['allow_self_signed'] = true;
                $mail['transportOptions']['options']['ssl']['verify_peer'] = false;
            }
        }
        $config['components']['mail'] = $mail;
        */
        
        // Add Theme
        /*
        $theme = Setting::Get('theme');
        if ($theme && $theme != "") {
            $config['theme'] = $theme;
        } else {
            unset($config['theme']);
        }
        */
        
        self::save($config);
    }

    public function getConfigFilePath()
    {
        return Yii::getAlias(Yii::$app->params['dynamicConfigFile']);
    }

}
