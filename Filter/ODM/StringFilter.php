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

class StringFilter extends Filter
{

    public function filter($queryBuilder, $alias, $field, $value)
    {
        if ($value == null) {
            return;
        }

        $value      = sprintf($this->getOption('format'), $value);

        $queryBuilder->field($field)->equals(new \MongoRegex($value));
    }

    public function getDefaultOptions()
    {
        return array(
            'format'   => '/.*%s.*/i'
        );
    }

   public function defineFieldBuilder(FormFactory $formFactory)
   {
       $options = $this->fieldDescription->getOption('filter_field_options', array('required' => false));

       $this->field = $formFactory->createNamedBuilder('text', $this->getName(), null, $options)->getForm();
   }
}