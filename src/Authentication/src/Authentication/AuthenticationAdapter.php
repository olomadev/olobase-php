<?php

declare(strict_types=1);

namespace Authentication\Authentication;

use Laminas\Db\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Predicate\Operator as SqlOp;
use Laminas\Authentication\Adapter\DbTable\CallbackCheckAdapter;

class AuthenticationAdapter extends CallbackCheckAdapter
{
    /**
     * _authenticateCreateSelect() - This method creates a Laminas\Db\Sql\Select object that
     * is completely configured to be queried against the database.
     *
     * @return Sql\Select
     */
    protected function authenticateCreateSelect()
    {
        $tableAlias = 'u';

        // get select
        $dbSelect = clone $this->getDbSelect();
        $dbSelect->from([$tableAlias => $this->tableName])
            ->columns([Sql\Select::SQL_STAR])
            ->join(
                ['ua' => 'userAvatars'],
                'ua.userId = '.$tableAlias.'.id',
                [
                    'avatar' => new Expression("TO_BASE64(avatarImage)"),
                    'mimeType',
                ],
                $dbSelect::JOIN_LEFT
            )
            ->where(new SqlOp($this->identityColumn, '=', $this->identity));

        // ->where(['client_id' => CLIENT_ID]);

        // Debug SQL Output:
        // echo $dbSelect->getSqlString($this->laminasDb->getPlatform());
        // die;

        return $dbSelect;
    }
}
