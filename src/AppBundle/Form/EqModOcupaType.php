<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class EqModOcupaType extends AbstractType {

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
                ->add('modOcupa', EntityType::class, array(
                    "label" => 'Modo Ocupación Unificada',
                    'class' => 'AppBundle:ModOcupa',
                    'placeholder' => 'Seleccione Modo Ocupación....',
                    'query_builder' => function (\AppBundle\Repository\ModOcupaRepository $er) {
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
            'data_class' => 'AppBundle\Entity\EqModOcupa'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'appbundle_eqmodocupa';
    }

}
