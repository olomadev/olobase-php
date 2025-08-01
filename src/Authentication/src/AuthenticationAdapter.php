<?php

declare(strict_types=1);

namespace Authentication;

use Laminas\Db\Sql;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Predicate\Operator as SqlOp;
use Laminas\Authentication\Adapter\DbTable\CallbackCheckAdapter;

class AuthenticationAdapter extends CallbackCheckAdapter
{
    /**
     * This method creates a Laminas\Db\Sql\Select object that
     * is completely configured to be queried against the database.
     *
     * @return Sql\Select
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
