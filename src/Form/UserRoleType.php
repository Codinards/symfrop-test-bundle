<?php

namespace Njeaner\Symfrop\Form;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
class UserRoleType extends SymfropAbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->setBuilder($builder);

        $this
            ->add('role', EntityType::class, [
                'choice_label' => 'title',
                'class' => $this->config->getRoleEntity(),
                'row_attr' => [
                    'class' => 'form-group'
                ],
                'label_attr' => ['class' => 'display-block text-white'],
                'attr' => ['class' => 'input'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => $this->config->getUserEntity()
            ]
        );
    }
}
