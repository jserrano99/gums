<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class EqFcoType extends AbstractType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('edificio', EntityType::class, array(
                    "label" => 'Edificio',
                    'class' => 'AppBundle:Edificio',
                    'placeholder' => 'Seleccione Edificio....',
                    'query_builder' => function (\AppBundle\Repository\EdificioRepository $er) {
                        return $er->createAlphabeticalQueryBuilder();
                    },
                    'required' => false,
                    'disabled' => false,
                    "attr" => array("class" => "form-control medio")))
                ->add('codigoLoc', TextType::class, array(
                    "label" => 'Código Local',
                    "required" => 'required',
                    "attr" => array("class" => "form-control muycorto")))
                ->add('fco', EntityType::class, array(
                    "label" => 'Forma Contratación Unificada',
                    'class' => 'AppBundle:Fco',
                    'placeholder' => 'Seleccione Forma Contratacion....',
                    'query_builder' => function (\AppBundle\Repository\FcoRepository $er) {
                        return $er->createAlphabeticalQueryBuilder();
                    },
                    'required' => false,
                    'disabled' => false,
                    "attr" => array("class" => "form-control corto")))
                            
                ->add('Guardar', SubmitType::class, array(
                    "attr" => array("class" => "form-submit btn btn-t btn-success")))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\EqFco'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'appbundle_eqmodocupa';
    }

}
