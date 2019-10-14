<?php
/**
 * Created by PhpStorm.
 * User: Виталий Шонов
 * Date: 17.05.2019
 * Time: 17:00
 */

namespace App\Contracts\Facades;
use App\Helpers\ChannelWriter;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Illuminate\Log\Writer
 */
class Logger extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ChannelWriter::class;
    }
}
