<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Builder\ODM;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\ORM\FieldDescription;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Form\Type\AdminType;
use Sonata\AdminBundle\Form\Type\ModelType;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormFactoryInterface;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;

class FormContractor implements FormContractorInterface
{

    protected $fieldFactory;

    protected $validator;

    /**
     * built-in definition
     *
     * @var array
     */
    protected $formTypes = array(
        'string'     =>  'text',
        'text'       =>  'textarea',
        'boolean'    =>  'checkbox',
        'checkbox'   =>  'checkbox',
        'integer'    =>  'integer',
        'tinyint'    =>  'integer',
        'smallint'   =>  'integer',
        'mediumint'  =>  'integer',
        'bigint'     =>  'integer',
        'decimal'    =>  'number',
        'datetime'   =>  'datetime',
        'date'       =>  'date',
        'choice'     =>  'choice',
        'array'      =>  'collection',
        'country'    =>  'country',
    );

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * Returns the field associated to a FieldDescriptionInterface
     *   ie : build the embedded form from the related AdminInterface instance
     *
     * @throws RuntimeException
     * @param \Symfony\Component\Form\FormBuilder $formBuilder
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @param null $fieldName
     * @return FieldGroup
     */
    protected function defineChildFormBuilder(FormBuilder $formBuilder, FieldDescriptionInterface $fieldDescription, $fieldName = null)
    {
        $fieldName = $fieldName ?: $fieldDescription->getFieldName();

        $associatedAdmin = $fieldDescription->getAssociationAdmin();

        if (!$associatedAdmin) {
            throw new \RuntimeException(sprintf('inline mode for field `%s` required an Admin definition', $fieldName));
        }

        // retrieve the related object
        $childBuilder = $formBuilder->create($fieldName, new AdminType(), array(
            'field_description' => $fieldDescription
        ));

        $formBuilder->add($childBuilder);

        $associatedAdmin->defineFormBuilder($childBuilder);
    }


    /**
     * Returns the class associated to a FieldDescriptionInterface if any defined
     *
     * @throws RuntimeException
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return bool|string
     */
    public function getFormTypeName(FieldDescriptionInterface $fieldDescription)
    {
        $typeName = false;

        // the user redefined the mapping type, use the default built in definition
        if (!$fieldDescription->getFieldMapping() || $fieldDescription->getType() != $fieldDescription->getMappingType()) {
            $typeName = array_key_exists($fieldDescription->getType(), $this->formTypes) ? $this->formTypes[$fieldDescription->getType()] : false;
        } else if ($fieldDescription->getOption('form_field_type', false)) {
            $typeName = $fieldDescription->getOption('form_field_type', false);
        } else if (array_key_exists($fieldDescription->getType(), $this->formTypes)) {
            $typeName = $this->formTypes[$fieldDescription->getType()];
        }

        if (!$typeName) {
            throw new \RuntimeException(sprintf('No known form type for field `%s` (`%s`) is not implemented', $fieldDescription->getFieldName(), $fieldDescription->getType()));
        }

        return $typeName;
    }

    /**
     * Add a new field type into the provided FormBuilder
     *
     * @param \Symfony\Component\Form\FormBuilder $formBuilder
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return void
     */
    public function addField(FormBuilder $formBuilder, FieldDescriptionInterface $fieldDescription)
    {
        switch ($fieldDescription->getType()) {
            case ClassMetadata::REFERENCE_ONE:
            case ClassMetadata::REFERENCE_MANY:
            case ClassMetadata::MANY:
            case ClassMetadata::ONE:
                throw new \RuntimeException('Type not implemented yet');

            default:
                $formBuilder->add(
                    $fieldDescription->getFieldName(),
                    $this->getFormTypeName($fieldDescription),
                    $fieldDescription->getOption('form_field_options', array())
                );
        }
    }

    /**
     * The method defines the correct default settings for the provided FieldDescription
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @param array $options
     * @return void
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription, array $options = array())
    {
        $fieldDescription->mergeOptions($options);

        if ($admin->getModelManager()->hasMetadata($admin->getClass()))
        {
            $metadata = $admin->getModelManager()->getMetadata($admin->getClass());

            // set the default field mapping
            if (isset($metadata->fieldMappings[$fieldDescription->getName()])) {
                $fieldDescription->setFieldMapping($metadata->fieldMappings[$fieldDescription->getName()]);
            }

            // set the default association mapping
            if (isset($metadata->associationMappings[$fieldDescription->getName()])) {
                $fieldDescription->setAssociationMapping($metadata->associationMappings[$fieldDescription->getName()]);
            }
        }

        if (!$fieldDescription->getType()) {
            throw new \RuntimeException(sprintf('Please define a type for field `%s` in `%s`', $fieldDescription->getName(), get_class($admin)));
        }

        $fieldDescription->setAdmin($admin);
        $fieldDescription->setOption('edit', $fieldDescription->getOption('edit', 'standard'));

        // fix template value for doctrine association fields
        if (!$fieldDescription->getTemplate()) {
             $fieldDescription->setTemplate(sprintf('SonataAdminBundle:CRUD:edit_%s.html.twig', $fieldDescription->getType()));
        }

        if ($fieldDescription->getType() == ClassMetadata::REFERENCE_ONE) {
            throw new \RuntimeException('Type not implemented yet');
            $fieldDescription->setTemplate('SonataAdminBundle:CRUD:edit_orm_one_to_one.html.twig');
            $admin->attachAdminClass($fieldDescription);
        }

        if ($fieldDescription->getType() == ClassMetadata::REFERENCE_MANY) {
            throw new \RuntimeException('Type not implemented yet');
            $fieldDescription->setTemplate('SonataAdminBundle:CRUD:edit_orm_many_to_one.html.twig');
            $admin->attachAdminClass($fieldDescription);
        }

        if ($fieldDescription->getType() == ClassMetadata::MANY) {
            throw new \RuntimeException('Type not implemented yet');
            $fieldDescription->setTemplate('SonataAdminBundle:CRUD:edit_orm_many_to_many.html.twig');
            $admin->attachAdminClass($fieldDescription);
        }

        if ($fieldDescription->getType() == ClassMetadata::ONE) {
            throw new \RuntimeException('Type not implemented yet');
            $fieldDescription->setTemplate('SonataAdminBundle:CRUD:edit_orm_one_to_many.html.twig');
            $admin->attachAdminClass($fieldDescription);
        }

        // set correct default value
        if ($fieldDescription->getType() == 'datetime') {
            $options = $fieldDescription->getOption('form_field_options', array());
            if (!isset($options['years'])) {
                $options['years'] = range(1900, 2100);
            }
            $fieldDescription->setOption('form_field', $options);
        }
    }

    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * @param string $name
     * @param array $options
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function getFormBuilder($name, array $options = array())
    {
        return $this->getFormFactory()->createNamedBuilder('form', $name, $options);
    }
}