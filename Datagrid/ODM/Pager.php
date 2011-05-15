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

use Sonata\AdminBundle\Datagrid\Pager as BasePager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;


class Pager extends BasePager
{
    protected $queryBuilder;

    protected $results;

    /**
     * Returns a query for counting the total results.
     *
     * @return integer
     */
    public function computeNbResult()
    {
        $countQuery = clone $this->getQuery();

        if (count($this->getParameters()) > 0) {
            $countQuery->setParameters($this->getParameters());
        }

        return $countQuery->count();
    }


    /**
     * Get all the results for the pager instance
     *
     * @param mixed $hydrationMode A hydration mode identifier
     * @return array
     */
    public function getResults()
    {
        return $this->getQuery()->execute();
    }

    /**
     * Get the query for the pager.
     *
     * @return \AdminBundle\Datagrid\ORM\ProxyQuery
     */
    public function getQuery()
    {
        return $this->query;
    }

    public function init()
    {
        $this->resetIterator();

        $this->setNbResults($this->computeNbResult());

        $this->getQuery()->setFirstResult(0);
        $this->getQuery()->setMaxResults(0);

        if (count($this->getParameters()) > 0) {
            $this->getQuery()->setParameters($this->getParameters());
        }

        if (0 == $this->getPage() || 0 == $this->getMaxPerPage() || 0 == $this->getNbResults()) {
            $this->setLastPage(0);
        } else {
            $offset = ($this->getPage() - 1) * $this->getMaxPerPage();

            $this->setLastPage(ceil($this->getNbResults() / $this->getMaxPerPage()));

            $this->getQuery()->setFirstResult($offset);
            $this->getQuery()->setMaxResults($this->getMaxPerPage());
        }
    }
}
