<?
namespace Honestdev\Eshop\Orm;

use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;

class TechnosadMonoSitesTable extends Entity\DataManager
{
    /**
     * @return string|null
     */
    public static function getTableName()
    {
        return 'technosad_monosites';
    }

    /**
     * @return array
     * @throws \Bitrix\Main\SystemException
     */
    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true
            ]),
            new Entity\StringField('UF_SITE_ID'),
            new Entity\StringField('UF_CODE'),
            new Entity\StringField('UF_NAME'),
            new Entity\StringField('UF_DOMAIN'),
            new Entity\StringField('UF_TYPE',['required' => false,'default_value'=>null]),
            new Entity\StringField('UF_ENTITY_ID',['required' => false,'default_value'=>null]),
        ];
    }
}
