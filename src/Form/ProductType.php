<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('keywords')
            ->add('description')
            ->add('type')
            ->add('discount')
            ->add('year')
            ->add('amount')
            ->add('price')
            ->add('sprice')
            ->add('min')
            ->add('detail')
            ->add('image')
            ->add('ancestor_id')
            ->add('category_id')
            ->add('user_id')
            ->add('status')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'csrf_protection'=>false,
        ]);
    }
}
