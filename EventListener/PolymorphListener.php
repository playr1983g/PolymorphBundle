<?php

namespace Devpunk\PolymorphBundle\EventListener;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;

class PolymorphListener implements EventSubscriber

{
    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(Events::postLoad);
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        /** @var EntityManager $em */
        $em = $args->getObjectManager();

        $emConfig = $em->getConfiguration();

        $entityClasses = $emConfig->getMetadataDriverImpl()->getAllClassNames();

        $reader = new AnnotationReader();
        $annotationReader = new CachedReader($reader, new ArrayCache());
        $annotations = [];

        foreach ($entityClasses as $entityClass) {
            $entityMetadata = $em->getClassMetadata($entityClass);
            $reflClass = $entityMetadata->getReflectionClass();

            $annotation = $annotationReader->getClassAnnotation(
                $reflClass,
                'Devpunk\PolymorphBundle\Annotation\Polymorph'
            );

            if (!$annotation) {
                continue;
            }
            $annotation->class = $entityClass;
            $annotations[] = $annotation;
        }

        if (!in_array('Devpunk\PolymorphBundle\Entity\PolymorphTrait', class_uses($object))) {
            return;
        }

        $object->initPolymorph($em, $annotations);
    }


}