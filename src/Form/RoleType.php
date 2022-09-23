<?php

namespace Njeaner\Symfrop\Form;


use Doctrine\Persistence\ObjectManager;
use Njeaner\Symfrop\Form\Validators\NotEmpty;
use Njeaner\Symfrop\Form\Validators\StringContains;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @author Jean Fils de Ntouoka 2 <nguimjeaner@gmail.com>
 * @version 1.0.0
 */
class RoleType extends SymfropAbstractType
{

    private static $manager;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->setBuilder($builder);

        $this
            ->add('name', TextType::class, [
                'row_attr' => [
                    'class' => 'form-group'
                ],
                'label_attr' => ['class' => 'display-block text-white'],
                'attr' => ['class' => 'input'],
                'constraints' => [
                    new StringContains('ROLE_', 0)
                ]
            ])
            ->add('title', TextType::class, [
                'row_attr' => [
                    'class' => 'form-group'
                ],
                'label_attr' => ['class' => 'display-block text-white'],
                'attr' => ['class' => 'input'],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('isDeletable', CheckboxType::class, [
                'row_attr' => [
                    'class' => 'form-group'
                ],
                'label_attr' => ['class' => 'text-white'],
            ])
            ->add('actions', EntityType::class, [
                'class' => $this->config->getActionEntity(),
                'choices' => self::$manager->getRepository($this->config->getActionEntity())->findBy(['hasAuth' => true]),
                'expanded' => true,
                'multiple' => true,
                'choice_label' => 'title',
                'row_attr' => [
                    'class' => 'form-group'
                ],
                'attr' => ['style' => 'margin-right: 10px;'],
                'label_attr' => ['class' => 'text-white'],
                'constraints' => [
                    new NotEmpty()
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->isRequired('entity_class');
        $resolver->setDefaults(
            [
                'data_class' => $this->config->getRoleEntity()
            ]
        );
    }

    public static function setManager(ObjectManager $manager)
    {
        self::$manager = $manager;
    }
}
