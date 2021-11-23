<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
<<<<<<< HEAD
            SlugField::new('slug')
                ->setTargetFieldName('name'),
            AssociationField::new('designer'),
            ImageField::new('image')
                ->setBasePath('uploads/products')
                ->setUploadDir('public/uploads/products')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),
            TextareaField::new('description'),
            AssociationField::new('category'),
            TextField::new('size'),
            MoneyField::new('price')
                ->setCurrency('EUR')
=======
            SlugField::new('slug')->setTargetFieldName('name'),
            AssociationField::new('designer'),
            ImageField::new('image')->setUploadDir('public/assets/img'),
            TextareaField::new('description'),
            AssociationField::new('category'),
            TextField::new('size'),
            MoneyField::new('price')->setCurrency('EUR')
>>>>>>> b29129fec2eb1b2fe4c98912b5d9c2ec6c3e5eb1
        ];
    }
}
