<?php

namespace Njeaner\Symfrop\Form;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 0.0.1
 */
class ActionType extends SymfropAbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->setBuilder($builder);

        $this
            ->add('title', TextType::class, [
                'row_attr' => [
                    'class' => 'form-group'
                ],
                'label_attr' => ['class' => 'display-block text-white'],
                'attr' => ['class' => 'input'],
            ])
            ->add('isUpdatable', CheckboxType::class, [
                'row_attr' => [
                    'class' => 'form-group'
                ],
                'label_attr' => ['class' => 'display-block text-white'],
                'attr' => ['class' => 'input'],
            ])
            ->add('hasAuth', CheckboxType::class, [
                'row_attr' => [
                    'class' => 'form-group'
                ],
                'label_attr' => ['class' => 'display-block text-white'],
                'attr' => ['class' => 'input'],
            ])
            ->add('isIndex', CheckboxType::class, [
                'row_attr' => [
                    'class' => 'form-group'
                ],
                'label_attr' => ['class' => 'display-block text-white'],
                'attr' => ['class' => 'input'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->isRequired('entity_class');
        $resolver->setDefaults(
            [
                'data_class' => $this->config->getActionEntity()
            ]
        );
    }
}
