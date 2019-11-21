<?php


namespace CircleLinkHealth\Core\Facades;


class Connection extends \Illuminate\Database\MySqlConnection {
    //@Override
    public function query() {
        return new QueryBuilder(
            $this,
            $this->getQueryGrammar(),
            $this->getPostProcessor()
        );
    }
}