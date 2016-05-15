#!/usr/bin/php
<?php

namespace kurt0015\installations;

/**
 * @Author: Kurt Aerts <me@kurtaerts.be>
 * @Copyright: 2016
 * 
 * This installation file makes it faster to install
 * the infoweb CMS.
 * 
 * REQUIRED
 * - Composer global installation
 * - run commands using exec
 * 
 * Complete installation instructions:
 * https://github.com/infoweb-internet-solutions/yii2-cms
 * 
 * Below you can configure you're installation config files
 * It will help you to add some default values to you're configuration
 * 
 * Templates special syntax
 * '{USE@\kartik\datecontrol\Module}' => true -> will be converted to use \kartik\datecontrol\Module;
 * '{LITERAL@\kartik\datecontrol\Module::FORMAT_DATE}' => 'php:d-m-Y' ->  will be converted to Module::FORMAT_DATE => 'php:d-m-Y'
 * 'class' => '{LITERAL@Zelenin\yii\modules\I18n\components\I18N::className()}' -> will be converted to 'class' => Zelenin\yii\modules\I18n\components\I18N::className()
 */

Templates::add('/environments/prod/common/config/main-local.php', [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host={DATABASE_HOST};dbname={DATABASE_NAME}',
            'username' => '{DATABASE_USER}',
            'password' => '{DATABASE_PASSWORD}',
            'charset' => 'utf8',
        ],
    ]
]);

Templates::add('/environments/dev/common/config/main-local.php', array_merge(
    Templates::get('/environments/prod/common/config/main-local.php'),
    [
        'controllerMap' => [
            'migrate' => [
                'class' => 'fishvision\migrate\controllers\MigrateController',
                'autoDiscover' => true,
                'migrationPaths' => [
                    '@vendor'
                ],
            ],
        ]
    ]
));

Templates::add('/frontend/web/uploads/img/.gitignore', <<<EOD
*
!.gitignore
EOD
);

Templates::add('/frontend/web/uploads/files/.gitignore', Templates::get('/frontend/web/uploads/img/.gitignore') );

Templates::add('/common/config/main.php', [
    'name' => '{PROJECT_NAME}',
    'language' => 'nl',
    'timeZone' => 'Europe/Brussels',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\DbCache',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        // Rewrite url's
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
        ],
        // Formatter
        'formatter' => [
            'dateFormat' => 'php:d-m-Y',
            'decimalSeparator' => ',',
            'thousandSeparator' => ' ',
            'currencyCode' => 'EUR',
        ],
        // Override views
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@dektrium/user/views' => '@infoweb/user/views'
                ],
            ],
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@infoweb/cms/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'host',
                'username' => 'user',
                'password' => 'password',
                'port' => 'port'
            ],
        ],
        'log' => [
            'traceLevel' => '{LITERAL@YII_DEBUG ? 3 : 0}',
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['error'],
                ],
                [
                    'class' => 'yii\log\EmailTarget',
                    'levels' => ['error'],
                    'categories' => ['yii\db\*'],
                    'message' => [
                       'from' => ['{EMAIL_FROM}'],
                       'to' => ['{EMAIL_TO}'],
                       'subject' => '[MySQL error @ domain.com]',
                    ],
                ],
            ],
        ],
        'i18n' => [
            'class' => '{LITERAL@Zelenin\yii\modules\I18n\components\I18N::className()}',
            'languages' => ['nl']
        ],
    ],
    'modules' => [
        'datecontrol' =>  [
            'class' => 'kartik\datecontrol\Module',

            // format settings for displaying each date attribute (ICU format example)
            'displaySettings' => [
                '{LITERAL@\kartik\datecontrol\Module::FORMAT_DATE}' => 'php:d-m-Y',
                '{LITERAL@\kartik\datecontrol\Module::FORMAT_TIME}' => 'php:H:i',
                '{LITERAL@\kartik\datecontrol\Module::FORMAT_DATETIME}' => 'dd-MM-yyyy HH:mm:ss',
            ],

            // format settings for saving each date attribute (PHP format example)
            'saveSettings' => [
                '{LITERAL@\kartik\datecontrol\Module::FORMAT_DATE}' => 'php:U', // saves as unix timestamp
                '{LITERAL@\kartik\datecontrol\Module::FORMAT_TIME}' => 'php:H:i:s',
                '{LITERAL@\kartik\datecontrol\Module::FORMAT_DATETIME}' => 'php:Y-m-d H:i:s',
            ],

            // set your display timezone
            'displayTimezone' => 'Europe/Brussels',

            // set your timezone for date saved to db
            'saveTimezone' => 'Europe/Brussels',

            // automatically use kartik\widgets for each of the above formats
            'autoWidget' => true,

            // default settings for each widget from kartik\widgets used when autoWidget is true
            'autoWidgetSettings' => [
                '{LITERAL@\kartik\datecontrol\Module::FORMAT_DATE}' => ['pluginOptions' => [
                    'autoclose' => true,
                    'todayHighlight' => true,
                    //'todayBtn' => true
                ]],
                '{LITERAL@\kartik\datecontrol\Module::FORMAT_DATETIME}' => [], // setup if needed
                '{LITERAL@\kartik\datecontrol\Module::FORMAT_TIME}' => [], // setup if needed
            ],
            // Use custom convert action
            'convertAction' => '/cms/parse/convert-date-control'
        ],
        'yii2images' => [
            'class' => 'rico\yii2images\Module',
            'imagesStorePath' => '@uploadsBasePath/img', //path to origin images
            'imagesCachePath' => '@uploadsBasePath/img/cache', //path to resized copies
            'graphicsLibrary' => 'GD', //but really its better to use 'Imagick'
            'placeHolderPath' => '@infoweb/cms/assets/img/transparent-placeholder.png',
        ],
    ],
    'params' => [
        // Font Awesome Icon framework
        'icon-framework' => 'fa',
    ],
]);

Templates::add('/backend/config/main.php', [
    'name' => '{PROJECT_NAME}',
    'bootstrap' => ['log','cms'],
    'modules' => [
        'cms' => [
            'class' => 'infoweb\cms\Module',
        ],
        'gridview' =>  [
            'class' => '\kartik\grid\Module'
        ],
        'media' => [
            'class' => 'infoweb\cms\Module',
        ],
        'email' => [
            'class' => 'infoweb\email\Module'
        ],
        'admin' => [
            'class' => 'mdm\admin\Module',
        ],
        'i18n' => [
            'class' => '{LITERAL@Zelenin\yii\modules\I18n\Module::className()}',
        ],
        'settings' => [
            'class' => 'infoweb\settings\Module'
        ],
        'pages' => [
            'class' => 'infoweb\pages\Module',
        ],
        'partials' => [
            'class' => 'infoweb\partials\Module',
        ],
        'seo' => [
            'class' => 'infoweb\seo\Module',
        ],
        'menu' => [
            'class' => 'infoweb\menu\Module',
        ],
        'alias' => [
            'class' => 'infoweb\alias\Module',
            'reservedUrls' => ['page'] // Url's that are reserved by the application
        ],
    ],
    'components' => [
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@app/views/layouts' => '@infoweb/cms/views/layouts',
                    '@dektrium/user/views' => '@infoweb/user/views'
                ],
            ],
        ],
        'request' => [
            'class' => 'common\components\Request',
            'web'=> '/backend/web',
            'adminUrl' => '/admin'
        ],
    ]
]);

Templates::add('/backend/config/params.php', [
    // Moximanager settings
    'moxiemanager'  => [
        'license-key'   => 'your-moxiemanager-key'
    ],
]);

Templates::add('/common/config/params.php', [
    // Enabled languages
    'languages' => [
        'nl'    => 'Nederlands',
        'fr'    => 'Français',
        'en'    => 'English',
    ],
    'companyName'   => '{PROJECT_NAME}'
]);

Templates::add('/frontend/config/main.php', [
    'components' => [
        'user' => [
            'identityClass' => 'infoweb\user\models\frontend\User',
            'enableAutoLogin' => true,
        ],
        'log' => [
            'traceLevel' => '{LITERAL@YII_DEBUG ? 3 : 0}',
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'request'=>[
            'class' => 'common\components\Request',
            'web' => '/frontend/web',
            'csrfParam' => '_frontendCSRF',
        ],
        // Override the urlManager component
        'urlManager' => [
            'class' => 'codemix\localeurls\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '404'               => 'site/error',
                '<alias:[\d\w\-]+>' => 'site/index',
            ],
        ],
        'page' => [
            'class' => 'infoweb\pages\components\Page'
        ]
    ],
]);

/**
 * 
 * Classes and stuff that is needed
 * you do not need to edit anything below this comment.
 * 
 */
define('CURRENT_PATH', realpath(dirname(__FILE__)));

class Templates {
    protected static $templates = [];

    public static function add($name, $value) {
        self::$templates[$name] = $value;
    }

    public static function get($name) {
        return isset(self::$templates[$name]) ? self::$templates[$name] : null;
    }
}

class Interaction {
    public static function input($question, $alternative = '', $required = false, $type = 'textline') {
        echo $question.' ';
        $input = trim(self::filter(fgets(STDIN)));

        if($required && $input == '') {
            echo 'Error: Field required!'."\n";
            self::input($question, $alternative, $required, $type);
        }

        if($input == '') {
            return $alternative;
        }

        return $input;
    }

    public static function filter($input) {
        return str_replace(["\n", "\r"], '', $input);
    }
}

class ArrayHelper {
    public static function arrayToCode($array, $return = true, $loop = 0) {
        $spaces = '    ';
        $startFormatting = '';
        for ($x = 0; $x <= $loop; $x++) {
            $startFormatting .= $spaces;
        }

        if(is_array($array) && count($array) == 0) {
            if (!$return) {
                print $startFormatting . "[]";
                return true;
            }

            return $startFormatting . "[]";
        }

        $string = "[";
        if (array_values($array) === $array) {
            $no_keys = true;
            foreach ($array as $value) {
                if(is_int($value)) {
                    $string .= "$value, ";
                }
                elseif (is_array($value)) {
                    $loop++;
                    $string .= self::arrayToCode($value, true, $loop) . ",\n";
                }
                elseif (is_string($value)) {
                    if(substr($value, 0, 9) == '{LITERAL@') {
                        $literal = rtrim(substr($value, 9), '}');
                        $string .= "$literal, ";
                    }
                    else {
                        $string .= "'$value', ";
                    }
                }
                else {
                    trigger_error("Unsupported type of \$value, in index $key.");
                }
            }
        }
        else {
            $string .="\n";
            foreach ($array as $key => $value) {
                $no_keys = false;

                if(is_string($value) && substr($value, 0, 9) == '{LITERAL@') {
                    $literal = rtrim(substr($value, 9), '}');
                    $string .= $startFormatting . "\"$key\" => $literal,\n";
                }
                elseif (is_int($value)) {
                    $string .= $startFormatting . "\"$key\" => $value,\n";
                }
                elseif (is_array($value)) {
                    $loop++;
                    $string .= $startFormatting . "\"$key\" => " . self::arrayToCode($value, true, $loop) . ",\n";
                }
                elseif (is_string($value)) {
                    $string .= $startFormatting . "\"$key\" => '$value',\n";
                }
                elseif (is_bool($value)) {
                    $string .= $startFormatting . "\"$key\" => (bool) ".(($value) ? 'true' : 'false').",\n";
                }
                else {
                    var_dump($value);
                    exit;
                    trigger_error("Unsupported type of \$value, in index $key.");
                }
            }
        }

        $string = substr($string, 0, strlen($string) - 2); # Remove last comma.
        if (!$no_keys) {
            $string .= "\n";
        }

        $string .= (array_values($array) === $array) ? "]" : $startFormatting . "]";

        if (!$return) {
            print $string;
            return true;
        }

        return $string;
    }
}

class Installation {
    protected $project_name;
    protected $project_folder;
    protected $include_git;
    protected $email;
    protected $dev_email;
    protected $database;
    protected $dev_database;

    public function __construct($gatherNeededInformation = true) {
        if($gatherNeededInformation) {
            $this->gatherNeededInformation();
        }

        $this->checks();
        $this->installation();
    }

    public function gatherNeededInformation() {
        $this->project_name = Interaction::input('Project name:', '', true);
        $this->project_folder = Interaction::input('Project directory:', '', true);
        $this->include_git = Interaction::input('Include GIT folder? y or n (default: n):', 'n');
        $this->_email();
        $this->_datababaseCredentials();
    }
    
    public function _email() {
        $this->email = [];
        $this->email['from'] = Interaction::input('Production email from (default: noreply@infoweb.be):', 'noreply@infoweb.be');
        $this->email['to'] = Interaction::input('Production email to:', '', true);

        /*
        $this->dev_email = [];

        if(Interaction::input('Use the same email settings for development? y or n (default: y):', 'y') == 'n') {
            $this->dev_email['from'] = Interaction::input('Development email from (default: noreply@infoweb.be):', 'noreply@infoweb.be');
            $this->dev_email['to'] = Interaction::input('Development email to:', '', true);
        }
        else {
            $this->dev_email['from'] = $this->email['from'];
            $this->dev_email['to'] = $this->email['to'];
        }*/
    }

    public function _datababaseCredentials() {
        $this->database = [];

        $this->database['host'] = Interaction::input('Production database host (default: localhost):', 'localhost');
        $this->database['port'] = Interaction::input('Production database port (default: 3306):', '3306');
        $this->database['name'] = Interaction::input('Production database name:', '', true);
        $this->database['user'] = Interaction::input('Production database user:', '', true);
        $this->database['password'] = Interaction::input('Production database password:', '', true);
        
        $this->dev_database = [];

        if(Interaction::input('Use the same database credentails for development? y or n (default: y):', 'y') == 'n') {
            $this->dev_database['host'] = Interaction::input('Development database host (default: localhost):', 'localhost');
            $this->dev_database['port'] = Interaction::input('Development database port (default: 3306):', '3306');
            $this->dev_database['name'] = Interaction::input('Development database name:', '', true);
            $this->dev_database['user'] = Interaction::input('Development database user:', '', true);
            $this->dev_database['password'] = Interaction::input('Development database password:', '', true);
        }
        else {
            $this->dev_database['host'] = $this->database['host'];
            $this->dev_database['port'] = $this->database['port'];
            $this->dev_database['name'] = $this->database['name'];
            $this->dev_database['user'] = $this->database['user'];
            $this->dev_database['password'] = $this->database['password'];
        }
    }

    public function checks() {
        if(!file_exists(CURRENT_PATH . '/'. $this->project_folder)) {
            if(!@mkdir(CURRENT_PATH . '/'. $this->project_folder, 755)) {
                if(Interaction::input('Can\'t create directory "'.CURRENT_PATH . '/'. $this->project_folder.'". Please check permissions. Retry? y or n') == 'y') {
                    $this->__construct(false);
                }
                else {
                    die('Goodbye!'."\n");
                }
            }
        }

        $databaseConnection = new \mysqli($this->database['host'], $this->database['user'], $this->database['password'], $this->database['name'], $this->database['port']);

        if ($databaseConnection->connect_errno) {
            if(Interaction::input('Database connection failed. Retry? y or n') == 'y') {
                $this->_datababaseCredentials();
            }
            else {
                die('Goodbye!'."\n");
            }
        }
    }

    public function installation() {
        $this->composer();
        $this->configFiles();
        $this->composerComponents();
        $this->createFolders();
        $this->initEnvironment();
        $this->extension();
    }

    /**
     * Installation
     */
    public function composer() {
        exec('composer global require "fxp/composer-asset-plugin:~1.0" --no-interaction && composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-advanced "'.addslashes($this->project_folder).'" --no-interaction');
    }

    /**
     * Configure environments
     */
    public function configFiles() {
        $configFiles = [
            '/environments/dev/common/config/main-local.php' => CURRENT_PATH . '/'. $this->project_folder . '/environments/dev/common/config/main-local.php',
            '/environments/prod/common/config/main-local.php' => CURRENT_PATH . '/'. $this->project_folder . '/environments/prod/common/config/main-local.php',
        ];

        foreach($configFiles as $template => $configFile) {
            $currentConfig = [];
            if(file_exists($configFile)) {
                $currentConfig = include($configFile);
            }

            if(!is_array($currentConfig)) {
                $currentConfig = [];  
            }

            $config = array_merge($currentConfig, Templates::get($template));
            $contents = <<<EOD
<?php

return 
EOD;

            $contents .= ArrayHelper::arrayToCode( $config, true ).';';
            $database = ($template == '/environments/prod/common/config/main-local.php') ? $this->database : $this->dev_database;

            // overwrite files with default configuration;
            $contents = str_replace([
                '{DATABASE_HOST}',
                '{DATABASE_NAME}',
                '{DATABASE_USER}',
                '{DATABASE_PASSWORD}'
            ], [
                $database['host'].':'.$database['port'],
                $database['name'],
                $database['user'],
                $database['password']
            ], $contents);

            file_put_contents($configFile, $contents);
        }
    }

    /**
     * Update composer.json file
     */
    public function composerComponents() {
        $cd = 'cd '.CURRENT_PATH . '/'. $this->project_folder . '/';

        if($this->include_git == 'y') {
            exec($cd.' && composer config preferred-install source');
        }

        exec($cd.' && composer config repositories.i18n vcs https://github.com/infoweb-internet-solutions/yii2-i18n-module');
        exec($cd.' && composer config repositories.ckeditor vcs https://github.com/infoweb-internet-solutions/yii2-ckeditor');

        exec($cd.' && composer require fishvision/yii2-migrate:* && composer require infoweb-internet-solutions/yii2-cms');
    }

    /**
     * Init environment: Part 1
     */
    public function createFolders() {
        $createFolders = [
            '/frontend/web/uploads/img/.gitignore' => CURRENT_PATH . '/' . $this->project_folder . '/frontend/web/uploads/img',
            '/frontend/web/uploads/files/.gitignore' => CURRENT_PATH . '/' . $this->project_folder . '/frontend/web/uploads/files'
        ];

        mkdir(CURRENT_PATH . '/' . $this->project_folder . '/frontend/web/uploads', 777);

        foreach($createFolders as $template => $createFolder) {
            $gitignore = Templates::get($template);
            mkdir($createFolder, 777);
            file_put_contents($createFolder . '/.gitignore', $gitignore);
        }
    }

    /**
     * Init environment: Part 2
     */
    public function initEnvironment() {
        $files = [
            '/common/config/params.php' => CURRENT_PATH . '/' . $this->project_folder . '/common/config/params.php',
            '/backend/config/params.php' => CURRENT_PATH . '/' . $this->project_folder . '/backend/config/params.php',
            '/frontend/config/params.php' => CURRENT_PATH . '/' . $this->project_folder . '/frontend/config/params.php',
            '/console/config/params.php' => CURRENT_PATH . '/' . $this->project_folder . '/console/config/params.php'
        ];

        foreach($files as $file) {
            if(file_exists($file)) {
                $config = include($file);

                if(is_array($config)) {
                    unset($config['adminEmail'], $config['supportEmail']);

                    $contents = <<<EOD
<?php

return 
EOD;

                    $contents .= ArrayHelper::arrayToCode($config, true).';';
                    file_put_contents($file, $contents);
                }
            }
        }

        $cd = 'cd '.CURRENT_PATH . '/'. $this->project_folder . '/';
        exec($cd.' && php yii --env=Production --overwrite=All');
    }
    
    
   /**
    * Usage
    */
    public function extension() {
        $files = [
            '/common/config/main.php' => CURRENT_PATH . '/' . $this->project_folder . '/common/config/main.php',
            '/backend/config/main.php' => CURRENT_PATH . '/' . $this->project_folder . '/backend/config/main.php',
            '/backend/config/params.php' => CURRENT_PATH . '/' . $this->project_folder . '/backend/config/params.php',
            '/common/config/params.php' => CURRENT_PATH . '/' . $this->project_folder . '/common/config/params.php',
            '/frontend/config/main.php' => CURRENT_PATH . '/' . $this->project_folder . '/frontend/config/main.php',
        ];

         foreach($files as $template => $file) {
            $currentConfig = [];
            if(file_exists($file)) {
                $currentConfig = include($file);
            }

            if(!is_array($currentConfig)) {
                $currentConfig = [];  
            }

            $config = array_merge($currentConfig, Templates::get($template));
            $contents = <<<EOD
<?php

return 
EOD;

            $contents .= ArrayHelper::arrayToCode( $config, true ).';';

            // overwrite files with default configuration;
            $contents = str_replace([
                '{PROJECT_NAME}',
                '{EMAIL_FROM}',
                '{EMAIL_TO}'
            ], [
                $this->project_name,
                $this->email['from'],
                $this->email['to']
            ], $contents);
            
            
            file_put_contents($file, $contents);
        }
    }
}

$installation = new Installation();

