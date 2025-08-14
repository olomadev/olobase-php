<?php

declare(strict_types=1);

namespace Authentication;

use Laminas\Authentication\Adapter\DbTable\CallbackCheckAdapter;
use Laminas\Db\Sql;
use Laminas\Db\Sql\Predicate\Operator as SqlOp;
use Laminas\Db\Sql\Select;

class AuthenticationAdapter extends CallbackCheckAdapter
{
    /**
     * This method creates a Laminas\Db\Sql\Select object that
     * is completely configured to be queried against the database.
     */
    protected function authenticateCreateSelect(): Select
    {
        // get select
        $dbSelect = clone $this->getDbSelect();
        $dbSelect->from(['u' => $this->tableName])
            ->columns([Sql\Select::SQL_STAR])
            ->where(new SqlOp($this->identityColumn, '=', $this->identity));

        return $dbSelect;
    }
}
