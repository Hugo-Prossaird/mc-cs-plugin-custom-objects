<?php


namespace MauticPlugin\CustomObjectsBundle\Tests\Functional\ApiPlatform;

use Doctrine\Common\Annotations\Annotation\IgnoreAnnotation;
use Doctrine\Common\Collections\ArrayCollection;
use Mautic\CategoryBundle\Entity\Category;
use Mautic\LeadBundle\Provider\FilterOperatorProviderInterface;
use MauticPlugin\CustomObjectsBundle\CustomFieldType\TextType;
use MauticPlugin\CustomObjectsBundle\Entity\CustomField;
use MauticPlugin\CustomObjectsBundle\Entity\CustomObject;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @IgnoreAnnotation("dataProvider")
 */
final class CustomItemFunctionalTest extends AbstractApiPlatformFunctionalTest
{
    /**
     * @dataProvider getCRUDProvider
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function testCustomItemCRUD(
        array $permissions,
        string $httpCreated,
        string $httpRetrieved,
        ?string $retrievedAlias,
        string $httpUpdated,
        ?string $updatedAlias,
        string $httpDeleted
    ): void {
        // USER
        $user = $this->getUser();
        // OBJECTS
        $customObject = $this->createCustomObject();
        $category = $this->createCategory();
        $customField = $this->createCustomField($customObject);
        // PERMISSION
        $this->setPermission($user, 'custom_objects:' . $customObject->getId(), $permissions);
        // CREATE
        $payloadCreate = $this->getCreatePayload($customObject, $category, $customField);
        $clientCreateResponse = $this->createEntity('custom_items', $payloadCreate);
        $this->assertEquals($httpCreated, $clientCreateResponse->getStatusCode());
        if (Response::HTTP_FORBIDDEN === $clientCreateResponse->getStatusCode()) {
            return;
        }
        // GET ID OF ENTITY
        $createdId = json_decode($clientCreateResponse->getContent())->{'@id'};
        // RETRIEVE
        $clientRetrieveResponse = $this->retrieveEntity($createdId);
        $this->assertEquals($httpRetrieved, $clientRetrieveResponse->getStatusCode());
        if ($retrievedAlias) {
            $this->assertEquals($retrievedAlias, json_decode($clientRetrieveResponse->getContent())->name);
        }
        // UPDATE
        $payloadUpdate = $this->getEditPayload();
        $clientUpdateResponse = $this->updateEntity($createdId, $payloadUpdate);
        $this->assertEquals($httpUpdated, $clientUpdateResponse->getStatusCode());
        if ($updatedAlias) {
            $this->assertEquals($updatedAlias, json_decode($clientUpdateResponse->getContent())->name);
        }
        // DELETE
        $clientDeleteResponse = $this->deleteEntity($createdId);
        $this->assertEquals($httpDeleted, $clientDeleteResponse->getStatusCode());
    }

    private function getCreatePayload(CustomObject $customObject, Category $category, CustomField $customField): array
    {
        return
            [
                "name" => "Custom Item Created",
                "customObject" => "/api/v2/custom_objects/" . $customObject->getId(),
                "language" => "en",
                "category" => "/api/v2/categories/" . $category->getId(),
                "fieldValues" =>
                    [
                        [
                            "id" => "/api/v2/custom_fields/" . $customField->getId(),
                            "value" => "test"
                        ]
                    ]
            ];
    }

    private function getEditPayload(): array
    {
        return
            [
                "name" => "Custom Item Edited"
            ];
    }

    /**
     * @see self::testCustomItemCRUD()
     *
     * @return array|array[]
     */
    public function getCRUDProvider(): array
    {
        return [
            "all_ok" =>
                [
                    ['viewown', 'viewother', 'editown', 'editother', 'create', 'deleteown', 'deleteother', 'publishown', 'publishother'],
                    Response::HTTP_CREATED,
                    Response::HTTP_OK,
                    "Custom Item Created",
                    Response::HTTP_OK,
                    "Custom Item Edited",
                    Response::HTTP_NO_CONTENT
                ],
            "no_delete" =>
                [
                    ['viewown', 'viewother', 'editown', 'editother', 'create', 'publishown', 'publishother'],
                    Response::HTTP_CREATED,
                    Response::HTTP_OK,
                    "Custom Item Created",
                    Response::HTTP_OK,
                    "Custom Item Edited",
                    Response::HTTP_FORBIDDEN
                ],
            "no_update" =>
                [
                    ['viewown', 'viewother', 'create', 'deleteown', 'deleteother', 'publishown', 'publishother'],
                    Response::HTTP_CREATED,
                    Response::HTTP_OK,
                    "Custom Item Created",
                    Response::HTTP_FORBIDDEN,
                    null,
                    Response::HTTP_NO_CONTENT
                ],
            "no_create" =>
                [
                    ['viewown', 'viewother', 'editown', 'editother', 'deleteown', 'deleteother', 'publishown', 'publishother'],
                    Response::HTTP_FORBIDDEN,
                    '',
                    null,
                    '',
                    null,
                    ''
                ],
        ];
    }

    private function createCustomObject(): CustomObject
    {
        $customObject = new CustomObject();
        $customObject->setNameSingular('Test custom object');
        $customObject->setNamePlural('Test custom objects');
        $customObject->setAlias('test_custom_object');
        $this->em->persist($customObject);
        $this->em->flush();
        return $customObject;
    }

    private function createCategory(): Category
    {
        $category = new Category();
        $category->setTitle('Test Category');
        $category->setDescription('Test Category Description');
        $category->setAlias('test_category');
        $category->setBundle('test_bundle');
        $this->em->persist($category);
        $this->em->flush();
        return $category;
    }

    private function createCustomField(CustomObject $customObject): CustomField
    {
        $translatorMock = $this->createMock(TranslatorInterface::class);
        $filterOperatorProviderMock = $this->createMock(FilterOperatorProviderInterface::class);
        $customFieldType = new TextType($translatorMock, $filterOperatorProviderMock);
        $customField = new CustomField();
        $customField->setLabel('Test Custom Field');
        $customField->setType('text');
        $customField->setTypeObject($customFieldType);
        $customField->setAlias('test_custom_field');
        $customField->setCustomObject($customObject);
        $this->em->persist($customField);
        $customObject->setCustomFields(new ArrayCollection([$customField]));
        $this->em->persist($customObject);
        $this->em->flush();
        return $customField;
    }
}
