<?php


namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;

class UsernameForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Twitter Username to load',
                'constraints' => [
                    new Regex([
                        'pattern' => '/[\w\d_]{1,15}/i',
                        'message' => 'Not a valid Twitter username',
                    ])
                ]
            ])
            ->add('show', SubmitType::class)
        ;
    }
}