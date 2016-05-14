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

Templates::add( '/frontend/web/uploads/files/.gitignore', Templates::get('/frontend/web/uploads/img/.gitignore') );

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
    public static function arrayToCode($array, $return = true) {
        if(count($array) == 0) {
            if (!$return) {
                print "[]";
                return true;
            }

            return "[]";
        }

        $string = "[";
        if (array_values($array) === $array) {
            $no_keys = true;
            foreach ($array as $value) {
                if(is_int($value)) {
                    $string .= "$value, ";
                }
                elseif (is_array($value)) {
                    $string .= printArrayInPHPFormat($value, true) . ",\n";
                }
                elseif (is_string($value)) {
                    $string .= "$value', ";
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
                if (is_int($value)) {
                    $string .= "\"$key\" => $value,\n";
                }
                elseif (is_array($value)) {
                    $string .= "\"$key\" => " . self::arrayToCode($value, true) . ",\n";
                }
                elseif (is_string($value)) {
                    $string .= "\"$key\" => '$value',\n";
                }
                elseif (is_bool($value)) {
                    $string .= "\"$key\" => bool ".(($value) ? 'true' : 'false').",\n";
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
        $string .= "]";

        if (!$return) {
            print $string;
            return true;
        }

        return $string;
    }
}

class Installation {
    protected $project_name;
    protected $include_git;
    protected $database;

    public function __construct($gatherNeededInformation = true) {
        if($gatherNeededInformation) {
            $this->gatherNeededInformation();
        }

        $this->checks();
        $this->installation();
    }

    public function gatherNeededInformation() {
        $this->project_name = Interaction::input('Project directory name:');
        $this->include_git = Interaction::input('Include GIT folder? y or n (default: n):', 'n');
        $this->_datababaseCredentials();
    }

    public function _datababaseCredentials() {
        $this->database = [];

        $this->database['host'] = Interaction::input('Production database host (default: localhost):', 'localhost');
        $this->database['port'] = Interaction::input('Production database port (default: 3306):', '3306');
        $this->database['name'] = Interaction::input('Production database name:', '', true);
        $this->database['user'] = Interaction::input('Production database user:', '', true);
        $this->database['password'] = Interaction::input('Production database password?', '', true);
        
        $this->dev_database = [];

        if(Interaction::input('Use the same database credentails for development? y or n (default: y):', 'y') == 'n') {
            $this->dev_database['host'] = Interaction::input('Development database host (default: localhost):', 'localhost');
            $this->dev_database['port'] = Interaction::input('Development database port (default: 3306):', '3306');
            $this->dev_database['name'] = Interaction::input('Development database name:', '', true);
            $this->dev_database['user'] = Interaction::input('Development database user:', '', true);
            $this->dev_database['password'] = Interaction::input('Development database password?', '', true);
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
        if(!file_exists(CURRENT_PATH . '/'. $this->project_name)) {
            if(!@mkdir(CURRENT_PATH . '/'. $this->project_name, 755)) {
                if(Interaction::input('Can\'t create directory "'.CURRENT_PATH . '/'. $this->project_name.'". Please check permissions. Retry? y or n') == 'y') {
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
    }

    public function composer() {
        exec('composer global require "fxp/composer-asset-plugin:~1.0" --no-interaction && composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-advanced "'.addslashes($this->project_name).'" --no-interaction');
    }

    public function configFiles() {
        $configFiles = [
            '/environments/dev/common/config/main-local.php' => CURRENT_PATH . '/'. $this->project_name . '/environments/dev/common/config/main-local.php',
            '/environments/prod/common/config/main-local.php' => CURRENT_PATH . '/'. $this->project_name . '/environments/prod/common/config/main-local.php',
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
            $contents = ArrayHelper::arrayToCode( $config, true );
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

    public function composerComponents() {
        $cd = 'cd '.CURRENT_PATH . '/'. $this->project_name . '/';

        if($this->include_git == 'y') {
            exec($cd.' && composer config preferred-install source');
        }

        exec($cd.' && composer require fishvision/yii2-migrate && composer require infoweb-internet-solutions/yii2-cms');

        exec($cd.' && composer config repositories vcs https://github.com/infoweb-internet-solutions/yii2-i18n-module');
        exec($cd.' && composer config repositories vcs https://github.com/infoweb-internet-solutions/yii2-ckeditor');
    }
    
    public function createFolders() {
        $createFolders = [
            '/frontend/web/uploads/img/.gitignore' => CURRENT_PATH . '/' . $this->project_name . '/frontend/web/uploads/img',
            '/frontend/web/uploads/files/.gitignore' => CURRENT_PATH . '/' . $this->project_name . '/frontend/web/uploads/files'
        ];

        mkdir(CURRENT_PATH . '/' . $this->project_name . '/frontend/web/uploads', 777);

        foreach($createFolders as $template => $createFolder) {
            $gitignore = Templates::get($template);
            mkdir($createFolder, 777);
            file_put_contents($createFolder . '/.gitignore', $gitignore);
        }
    }
}

$installation = new Installation();

