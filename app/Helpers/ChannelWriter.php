<?php
/**
 * Created by PhpStorm.
 * User: Виталий Шонов
 * Date: 17.05.2019
 * Time: 17:01
 */

//https://stackoverflow.com/questions/37809989/laravel-5-2-custom-log-file-for-different-tasks

namespace App\Helpers;

use InvalidArgumentException as InvalidArgumentExceptionAlias;
use Monolog\Logger;
use Carbon\Carbon;

class ChannelWriter
{
    protected $channel;

    /**
     * The Log channels.
     *
     * @var array
     */
    public $channels = [
        'default' => [
            'path' => 'logs/laravel.log',
            'level' => Logger::INFO
        ],
        'amo' => [
            'path' => 'logs/amo.log',
            'level' => Logger::INFO
        ],
        'acuity' => [
            'path' => 'logs/acuity.log',
            'level' => Logger::INFO
        ],
    ];

    /**
     * The Log levels.
     *
     * @var array
     */
    protected $levels = [
        'debug'     => Logger::DEBUG,
        'info'      => Logger::INFO,
        'notice'    => Logger::NOTICE,
        'warning'   => Logger::WARNING,
        'error'     => Logger::ERROR,
        'critical'  => Logger::CRITICAL,
        'alert'     => Logger::ALERT,
        'emergency' => Logger::EMERGENCY,
    ];

    public function __construct() {}

    public function channel($channel) {
        if(!array_key_exists($channel, $this->channels)){
            throw new InvalidArgumentExceptionAlias('Invalid channel used.');
        }

        $this->channel = $channel;
    }

    /**
     * Write to log based on the given channel and log level set
     *
     * @param $level
     * @param $message
     * @param array $context
     * @throws \Exception
     */
    public function writeLog($level, $message, $context)
    {
        if(is_string($context)) {
            $message = $context;
            $context = [];
        }
        if($this->channel === null){
            $this->channel = 'default';
        }

        $file = pathinfo($this->channels[$this->channel]['path']);
        $this->channels[$this->channel]['path'] = $file['dirname'] . '/' . $file['filename'] . '-' . Carbon::now()->format('Y-m-d') . '.' . $file['extension'];

        if(!isset($this->channels[$this->channel]['_instance']) ){
            $this->channels[$this->channel]['_instance'] = new Logger($this->channel);
            $this->channels[$this->channel]['_instance']->pushHandler(
                new ChannelStreamHandler(
                    $this->channel,
                    storage_path() .'/'. $this->channels[$this->channel]['path'],
                    $this->channels[$this->channel]['level']
                )
            );
        }

        $this->channels[$this->channel]['_instance']->{$level}($message, $context);
    }

    public function info($context = [])
    {
        $level = array_flip($this->levels)[$this->channels[$this->channel]['level']];
        $this->writeLog($level, null, $context);
    }

    public function __call($func, $params){
        if(array_key_exists($func, $this->levels)){
            return $this->writeLog($params[0], $func, $params[1]);
        }
    }
}
