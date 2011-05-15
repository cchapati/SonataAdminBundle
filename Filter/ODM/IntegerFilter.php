<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Filter\ODM;

use Symfony\Component\Form\FormFactory;

class IntegerFilter extends Filter
{
    public function filter($queryBuilder, $alias, $field, $value)
    {
        if ($value == null) {
            return;
        }

        $validOperators = array(
            '>' => 'gt',
            '>=' => 'gte',
            '<' => 'lt',
            '<=' => 'lte',
            '=' =>'equals'
        );

        if (!isset($validOperators[$this->getOption('operator')])) {
            throw new \RuntimeException('Invalid operator');
        }

        $operator = $validOperators[$this->getOption('operator')];

        call_user_func(array($queryBuilder->field($field), $operator), $value);
    }

    public function getDefaultOptions()
    {
        return array(
            'operator' => '=',
            'format'   => '%d'
        );
    }

   public function defineFieldBuilder(FormFactory $formFactory)
   {
       $options = $this->fieldDescription->getOption('filter_field_options', array('required' => false));

       $this->field = $formFactory->createNamedBuilder('text', $this->getName(), null, $options)->getForm();
   }
}