<?php

namespace App\Form;

use App\Entity\Image;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageType extends FileType
{
    private $imagePath;
    
    /**
     * @param string $imagePath
     * !!!     $imagePath is defined in services.yaml    !!!
     */
    public function __construct(string $imagePath)
    {
        $this->imagePath = $imagePath;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
//        $builder->addModelTransformer(new CallbackTransformer(
//            function(Image $image = null) {
//                if ($image instanceof Image) {
//                    return new File($this->imagePath . $image->getFile());
//                }
//            },
//            function(UploadedFile $uploadedFile = null) {
//                if ($uploadedFile instanceof UploadedFile) {
//                    $image = new Image();
//                    $image->setFile($uploadedFile);
//                    return $image;
//                }
//            }
//        ));
    }
//    public function getBlockPrefix()
//    {
//        return 'image';
//    }
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'required' => false
        ]);
    }
}