<?php
namespace Honestdev\Eshop\Repository;

use Honestdev\Eshop\Model\MonoSiteModel;
use Honestdev\Eshop\Orm\TechnosadMonoSitesTable;

class MonoSitesRepository
{
    /**
     * @var string
     */
    protected static string $table = TechnosadMonoSitesTable::class;

    /**
     * @return MonoSiteModel
     */
    public function getModel(): MonoSiteModel
    {
        return new MonoSiteModel();
    }

    /**
     * @param array $params
     * @return array
     */
    public function  getList(array $params = []): array
    {
        return static::$table::getList($params)->fetchAll();
    }

    /**
     * @param array $params
     * @return MonoSiteModel|null
     */
    public function getElement(array $params = []): ?MonoSiteModel
    {
        return current($this->getElements($params)) ?: null;
    }

    /**
     * @param array $params
     * @param bool $useModel
     * @return array
     */
    public function getElements(array $params = [], $useModel = true): array
    {
        $arElements = [];

        if(!empty($params['select'])) {
            $params['select'][] = 'ID';
        }

        foreach (static::$table::getList($params)->fetchAll() as $arMonoSite) {
            $arElements[] = $useModel ? $this->createElementFromArray($arMonoSite) : $arMonoSite;
        }

        return $arElements;
    }

    /**
     * @param array $arElement
     * @return MonoSiteModel|null
     */
    public function createElementFromArray(array $arElement): ?MonoSiteModel
    {
        $element = $this->getModel();

        $element
            ->setSiteId((string)$arElement['UF_SITE_ID'])
            ->setCode((string)$arElement['UF_CODE'])
            ->setName((string)$arElement['UF_NAME'])
            ->setDomain((string)$arElement['UF_DOMAIN'])
            ->setType((string)$arElement['UF_TYPE'])
            ->setEntityId((string)$arElement['UF_ENTITY_ID'])
        ;

        if ((int)$arElement['ID']) {
            $element->setId((int)$arElement['ID']);
        }

        return $element;
    }

    /**
     * @param MonoSiteModel $element
     * @return array
     */
    public function createArrayFromElement(MonoSiteModel $element): array
    {
        return [
            'ID' => (int)$element->getId() ?: null,
            'UF_SITE_ID' => $element->getSiteId(),
            'UF_CODE' => $element->getCode(),
            'UF_NAME' => $element->getName(),
            'UF_DOMAIN' => $element->getDomain(),
            'UF_TYPE' => $element->getType(),
            'UF_ENTITY_ID' => $element->getEntityId(),
        ];
    }

    /**
     * @param MonoSiteModel $element
     * @return MonoSiteModel|null
     */
    public function addElement(MonoSiteModel $element): ?MonoSiteModel
    {
        $result = static::$table::add($this->createArrayFromElement($element));

        if (!$result->isSuccess() || !(int)$result->getId()) {
            throw new \RuntimeException(current($result->getErrorMessages()) ?: 'Произошла ошибка добавления записи');
        }

        $element->setId($result->getId());
        return $element;
    }

    /**
     * @param MonoSiteModel $element
     * @return MonoSiteModel|null
     */
    public function updateElement(MonoSiteModel $element): ?MonoSiteModel
    {

        $result = static::$table::update($element->getId(), $this->createArrayFromElement($element));

        if (!$result->isSuccess() || !(int)$result->getId()) {
            throw new \RuntimeException(current($result->getErrorMessages()) ?: 'Произошла ошибка обновления записи');
        }

        $element->setId($result->getId());
        return $element;
    }

    /**
     * @param MonoSiteModel $element
     * @return MonoSiteModel|null
     */
    public function saveElement(MonoSiteModel $element): ?MonoSiteModel
    {
        $method = (int)$element->getId() ? 'updateElement' : 'addElement';
        return $this->$method($element);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id): bool
    {
        $result = static::$table::delete($id);

        if ($result->isSuccess()) {
            throw new \RuntimeException(current($result->getErrorMessages()) ?: 'Произошла ошибка удаления транзакции');
        }

        return true;
    }

    /**
     * @param MonoSiteModel $element
     * @return bool
     */
    public function deleteElement(MonoSiteModel $element): bool
    {
        return $this->deleteById($element->getId());
    }
}