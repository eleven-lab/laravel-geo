<?php

namespace ElevenLab\GeoLaravel\Database\Query;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Processors\Processor;
use ElevenLab\GeoLaravel\Database\Schema\Grammars\MySqlGrammar;

class Builder extends \Illuminate\Database\Query\Builder
{
    public function __construct(ConnectionInterface $connection, MySqlGrammar $grammar, Processor $processor)
    {
        parent::__construct($connection, $grammar, $processor);
    }
}