<?php

namespace Devpunk\PolymorphBundle\Entity;

use Devpunk\PolymorphBundle\Annotation\Polymorph;
use Doctrine\ORM\EntityManager;

trait PolymorphTrait
{
    /** @var  Polymorph[] */
    private $properties;

    /** @var  EntityManager */
    private $em;

    public function __call($method, $params)
    {
        $reflClass = new \ReflectionClass($this);
        $namespace = $reflClass->getNamespaceName();

        foreach ($this->properties as $p) {
            $typeField = $p->typeField;
            $field = $p->field;

            $targetEntity = $p->targetEntity;
            $targets = [
                $targetEntity,
                $namespace . "\\" . $targetEntity
            ];

            if (!in_array(get_class($this), $targets)) {
                continue;
            }

            $getter = str_replace("Id", "", $p->field);
            $getter = str_replace("id", "", $getter);
            $getter = sprintf("get%s", ucfirst($getter));

            if ($method != $getter) {
                continue;
            }

            $typeId = $this->getValueByReflection($reflClass, $typeField);
            $id = $this->getValueByReflection($reflClass, $field);

            if ($p->typeValue != $typeId) {
                continue;
            }

            $obj = $this->em->getRepository($p->class)->find($id);

            return $obj;
        }


    }

    private function getValueByReflection($reflClass, $key)
    {
        $value = $reflClass->getProperty($key);
        $value->setAccessible(true);

        return $value->getValue($this);
    }

    public function initPolymorph(EntityManager $em, array $properties)
    {
        $this->em = $em;

        $this->properties = $properties;
    }
} 