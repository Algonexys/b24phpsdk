<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Userfieldconfig\Service;

use Bitrix24\SDK\Services\CRM\Type\Service\Type;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Service\Userfieldconfig;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Userfieldconfig::class)]
class UserfieldconfigTest extends TestCase
{
    private Userfieldconfig $userfieldConfigService;

    private Type $typeService;

    private int $scratchTypeId;

    private string $entityId;

    #[\Override]
    protected function setUp(): void
    {
        $this->userfieldConfigService = Factory::getServiceBuilder()->getCRMScope()->userfieldConfig();
        $this->typeService = Factory::getServiceBuilder()->getCRMScope()->type();

        $addedType = $this->typeService->add(sprintf('%s userfieldconfig test SPA type', time()));
        $this->scratchTypeId = $addedType->getId();
        $this->entityId = sprintf('CRM_%d', $addedType->type()->entityTypeId);
    }

    #[\Override]
    protected function tearDown(): void
    {
        // deleting the SPA type also removes its custom fields
        $this->typeService->delete($this->scratchTypeId);
    }

    public function testAdd(): void
    {
        $fieldName = sprintf('UF_%s_%d', $this->entityId, time());
        $result = $this->userfieldConfigService->add('crm', [
            'entityId' => $this->entityId,
            'fieldName' => $fieldName,
            'userTypeId' => 'string',
        ]);

        $this->assertEquals($fieldName, $result->field()->fieldName);
        $this->assertEquals($this->entityId, $result->field()->entityId);
        $this->assertEquals('string', $result->field()->userTypeId);
    }

    public function testUpdate(): void
    {
        $fieldName = sprintf('UF_%s_%d', $this->entityId, time());
        $added = $this->userfieldConfigService->add('crm', [
            'entityId' => $this->entityId,
            'fieldName' => $fieldName,
            'userTypeId' => 'string',
        ]);

        $this->assertTrue(
            $this->userfieldConfigService->update('crm', $added->field()->id, ['mandatory' => 'Y'])->isSuccess()
        );

        $updated = $this->userfieldConfigService->get('crm', $added->field()->id);
        $this->assertTrue($updated->field()->mandatory);
    }

    public function testGet(): void
    {
        $fieldName = sprintf('UF_%s_%d', $this->entityId, time());
        $added = $this->userfieldConfigService->add('crm', [
            'entityId' => $this->entityId,
            'fieldName' => $fieldName,
            'userTypeId' => 'string',
        ]);

        $result = $this->userfieldConfigService->get('crm', $added->field()->id);
        $this->assertEquals($fieldName, $result->field()?->fieldName);
        $this->assertEquals($added->field()->id, $result->field()?->id);
    }

    public function testList(): void
    {
        $fieldName = sprintf('UF_%s_%d', $this->entityId, time());
        $added = $this->userfieldConfigService->add('crm', [
            'entityId' => $this->entityId,
            'fieldName' => $fieldName,
            'userTypeId' => 'string',
        ]);

        $items = $this->userfieldConfigService->list('crm', [], [], ['entityId' => $this->entityId])->getUserfieldConfigs();
        $this->assertNotEmpty($items);
        $this->assertEquals($fieldName, $items[0]->fieldName);
        $this->assertEquals($added->field()->id, $items[0]->id);
    }

    public function testDelete(): void
    {
        $fieldName = sprintf('UF_%s_%d', $this->entityId, time());
        $added = $this->userfieldConfigService->add('crm', [
            'entityId' => $this->entityId,
            'fieldName' => $fieldName,
            'userTypeId' => 'string',
        ]);

        $this->assertTrue($this->userfieldConfigService->delete('crm', $added->field()->id)->isSuccess());
    }

    public function testGetTypes(): void
    {
        $types = $this->userfieldConfigService->getTypes('crm')->getTypes();
        $this->assertArrayHasKey('string', $types);
        $this->assertEquals('string', $types['string']->userTypeId);
        $this->assertNotEmpty($types['string']->description);
    }
}
