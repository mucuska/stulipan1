<?php

namespace App\Form;

use App\Entity\InventorySupplyItem;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Form\InventoryProductFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InventorySupplyItemFormType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('product', InventoryProductFormType::class, [
                'label' => false,
                'disabled' => 'true',
                'required' => false,
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'Mennyiség',
                'attr' => ['placeholder' => ''],
                'required' => false,
            ])
            ->add('cog', NumberType::class, [
                'label' => 'Beszer. ár',
                'attr' => ['placeholder' => ''],
                'required' => false,
            ])
            ->add('markup', NumberType::class, [
                'label' => 'Szorzó',
                'attr' => ['placeholder' => ''],
                'required' => false,
            ])
            ->add('retailPrice', NumberType::class, [
                'label' => 'Eladási ár',
                'attr' => ['placeholder' => ''],
//                'required' => false,
            ])
            ->getForm();
		
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
						'data_class' => InventorySupplyItem::class
        ]);
    
    }

}
