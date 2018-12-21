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

namespace MauticPlugin\CustomObjectsBundle\Controller\CustomField;

use MauticPlugin\CustomObjectsBundle\Entity\CustomField;
use MauticPlugin\CustomObjectsBundle\Form\Type\CustomFieldType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Mautic\CoreBundle\Controller\CommonController;
use Symfony\Component\HttpFoundation\JsonResponse;
use MauticPlugin\CustomObjectsBundle\Provider\CustomFieldPermissionProvider;
use MauticPlugin\CustomObjectsBundle\Exception\ForbiddenException;
use MauticPlugin\CustomObjectsBundle\Provider\CustomFieldRouteProvider;

class NewController extends CommonController
{
    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var CustomFieldPermissionProvider
     */
    private $permissionProvider;

    /**
     * @var CustomFieldRouteProvider
     */
    private $routeProvider;

    /**
     * @param FormFactory $formFactory
     * @param CustomFieldPermissionProvider $permissionProvider
     * @param CustomFieldRouteProvider $routeProvider
     */
    public function __construct(
        FormFactory $formFactory,
        CustomFieldPermissionProvider $permissionProvider,
        CustomFieldRouteProvider $routeProvider
    )
    {
        $this->formFactory        = $formFactory;
        $this->permissionProvider = $permissionProvider;
        $this->routeProvider      = $routeProvider;
    }

    /**
     * @return Response|JsonResponse
     */
    public function renderFormAction()
    {
        try {
            $this->permissionProvider->canCreate();
        } catch (ForbiddenException $e) {
            $this->accessDenied(false, $e->getMessage());
        }
        
        $entity = new CustomField();
        $action = $this->routeProvider->buildSaveRoute();
        $form   = $this->formFactory->create(CustomFieldType::class, $entity, ['action' => $action]);

        return $this->delegateView(
            [
                'returnUrl'      => $this->routeProvider->buildListRoute(),
                'viewParameters' => [
                    'entity' => $entity,
                    'form'   => $form->createView(),
                ],
                'contentTemplate' => 'CustomObjectsBundle:CustomField:form.html.php',
                'passthroughVars' => [
                    'mauticContent' => 'customField',
                    'route'         => $this->routeProvider->buildNewRoute(),
                ],
            ]
        );
    }
}