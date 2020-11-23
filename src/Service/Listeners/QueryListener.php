<?php

namespace Levonliu\Packages\Service\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;


class QueryListener
{
    /**
     * Create the event QueryListener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function handle(QueryExecuted $event)
    {
        if (env('APP_DEBUG') == TRUE) {
            $sql = str_replace("?", "'%s'", $event->sql);
            $log = vsprintf($sql, $event->bindings);
            $log .= '  [RunTime:' . $event->time . 'ms]';
            (new Logger('sql'))->pushHandler(
                (new StreamHandler(
                    storage_path("logs/sql/sql.log"),
                    Logger::INFO
                ))->setFormatter(new LineFormatter(NULL, 'Y-m-d H:i:s', TRUE, TRUE))
            )->info($log);
        }
    }
}
