<?php

declare(strict_types=1);

/*
* @copyright   2020 Mautic, Inc. All rights reserved
* @author      Mautic, Inc.
*
* @link        https://mautic.com
*
* @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

namespace MauticPlugin\CustomObjectsBundle\EventListener;

use MauticPlugin\CustomObjectsBundle\CustomObjectEvents;
use MauticPlugin\CustomObjectsBundle\Entity\CustomObject;
use MauticPlugin\CustomObjectsBundle\Event\CustomObjectEvent;
use MauticPlugin\CustomObjectsBundle\Model\CustomObjectModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CustomObjectPostSaveSubscriber implements EventSubscriberInterface
{
    /**
     * @var CustomObjectModel
     */
    private $customObjectModel;

    public function __construct(CustomObjectModel $customObjectModel)
    {
        $this->customObjectModel = $customObjectModel;
    }

    public static function getSubscribedEvents()
    {
        return [
            CustomObjectEvents::ON_CUSTOM_OBJECT_POST_SAVE => 'postSave',
        ];
    }

    /**
     * Persists the relationship object to the
     */
    public function postSave(CustomObjectEvent $event): void
    {
        $object = $event->getCustomObject();

        if (CustomObject::TYPE_MASTER === $object->getType()) {
            return;
        }

        $object->getMasterObject()->setRelationshipObject($object);

        $this->customObjectModel->saveEntity($object->getMasterObject());
    }
}
