<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Datagrid\ODM;

use Doctrine\ODM\MongoDB\Query\Builder;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

/**
 * This class try to unify the query usage with Doctrine
 */
class ProxyQuery implements ProxyQueryInterface
{
    protected $queryBuilder;

    protected $sortBy;

    protected $sortOrder;

    public function __construct(Builder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function execute(array $params = array(), $hydrationMode = null)
    {
        // always clone the original queryBuilder
        $queryBuilder = clone $this->queryBuilder;

        return $queryBuilder->getQuery()->execute($params, $hydrationMode);
    }

    public function __call($name, $args)
    {
        return call_user_func_array(array($this->queryBuilder, $name), $args);
    }

    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;
    }

    public function getSortBy()
    {
        return $this->sortBy;
    }

    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }

    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    public function getSingleScalarResult()
    {
        $query = $this->queryBuilder->getQuery();

        return $query->getSingleScalarResult();
    }

    public function __clone()
    {
        $this->queryBuilder = clone $this->queryBuilder;
    }
}
