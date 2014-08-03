<?php

namespace Devpunk\PolymorphBundle\Annotation;


use Doctrine\Common\Annotations\Annotation;

/**
 *  * @Polymorph(targetEntity="ReservationItem", typeField="itemType", field="unit", typeValue="1")

 * @Annotation()
 */
class Polymorph
{
    public $targetEntity;

    public $typeField;

    public $field;

    public $typeValue;

    public $class;
}