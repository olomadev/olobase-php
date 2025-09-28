<?php

declare(strict_types=1);

namespace Authentication;

use Laminas\Db\Sql\Select;

use const select;

/**
 * You can extend this class in your application to customize the SQL query
 * used for user authentication.
 *
 * To customize the database selection logic, override the protected
 * `authenticateCreateSelect()` method in your subclass.
 */
class MyAuthenticationAdapter extends AuthenticationAdapter
{
    protected function authenticateCreateSelect(): Select
    {
        $select = parent::authenticateCreateSelect();
        // $select->where(['u.tenant_id' => 'your-tenant-id']);

        // Debug SQL Output:
        // echo $select->getSqlString($this->laminasDb->getPlatform());
        // die;
        return select;
    }
}