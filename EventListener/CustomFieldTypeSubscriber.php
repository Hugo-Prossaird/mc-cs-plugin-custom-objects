<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\CustomObjectsBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use MauticPlugin\CustomObjectsBundle\CustomFieldEvents;
use MauticPlugin\CustomObjectsBundle\CustomFieldType\DateTimeType;
use MauticPlugin\CustomObjectsBundle\CustomFieldType\DescriptionAreaType;
use MauticPlugin\CustomObjectsBundle\CustomFieldType\EmailType;
use MauticPlugin\CustomObjectsBundle\CustomFieldType\HiddenType;
use MauticPlugin\CustomObjectsBundle\CustomFieldType\HtmlAreaType;
use MauticPlugin\CustomObjectsBundle\Event\CustomFieldTypeEvent;
use MauticPlugin\CustomObjectsBundle\CustomFieldType\IntType;
use MauticPlugin\CustomObjectsBundle\CustomFieldType\TextType;
use MauticPlugin\CustomObjectsBundle\CustomFieldType\TextareaType;
use Symfony\Component\Translation\TranslatorInterface;

class CustomFieldTypeSubscriber extends CommonSubscriber
{
    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CustomFieldEvents::MAKE_FIELD_TYPE_LIST => ['makeFieldTypeList', 0],
        ];
    }

    /**
     * @todo add more field types
     * 
     * @param CustomFieldTypeEvent $event
     */
    public function makeFieldTypeList(CustomFieldTypeEvent $event)
    {
        $event->addCustomFieldType(new DateTimeType($this->translator->trans('custom.field.type.datetime')));
        $event->addCustomFieldType(new DescriptionAreaType($this->translator->trans('custom.field.type.description_area')));
        $event->addCustomFieldType(new EmailType($this->translator->trans('custom.field.type.email')));
        $event->addCustomFieldType(new HiddenType($this->translator->trans('custom.field.type.hidden')));
        $event->addCustomFieldType(new HtmlAreaType($this->translator->trans('custom.field.type.html_area')));
        $event->addCustomFieldType(new IntType($this->translator->trans('custom.field.type.int')));
        $event->addCustomFieldType(new TextType($this->translator->trans('custom.field.type.text')));
        $event->addCustomFieldType(new TextareaType($this->translator->trans('custom.field.type.textarea')));
    }
}
