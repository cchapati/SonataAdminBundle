<?php
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Datagrid;

/**
 * Interface used by the Datagrid to build the query
 */
interface ProxyQueryInterface
{
    /**
     * @abstract
     * @param array $params
     * @param null $hydrationMode
     * @return void
     */
    function execute(array $params = array(), $hydrationMode = null);

    /**
     * @abstract
     * @param string $name
     * @param array $args
     * @return void
     */
    function __call($name, $args);

    /**
     * @abstract
     * @param string $sortBy
     * @return void
     */
    function setSortBy($sortBy);

    /**
     * @abstract
     * @return string
     */
    function getSortBy();

    /**
     * @abstract
     * @param string $sortOrder
     * @return void
     */
    function setSortOrder($sortOrder);

    /**
     * @abstract
     * @return string
     */
    function getSortOrder();

    /**
     * @abstract
     * @return mixed
     */
    function getSingleScalarResult();
}
