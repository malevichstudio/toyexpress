<?php

namespace app\models;

use app\backgroundtasks\traits\SearchModelTrait;
use app\behaviors\Tree;
use app\modules\image\models\Image;
use app\modules\video\models\Video;
use app\modules\video\traits\SaveVideos;
use app\properties\HasProperties;
use app\traits\FindById;
use app\traits\GetImages;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "navigation".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property string $name
 * @property integer $sort_order
 * @property integer $type_id
 * @property string $css_class
 * @property string $params
 * @property string $view
 * Relations:
 * @property Menu[] $children
 * @property Menu $parent
 * @property Video[] $videos
 * @property Image[] $images
 */
class Menu extends ActiveRecord
{
    use GetImages;
    use SearchModelTrait;
    use FindById;
    use SaveVideos;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%menu}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['view', 'parent_id', 'type_id'], 'required'],
            [['parent_id', 'sort_order', 'type_id'], 'integer'],
            [['name'], 'string', 'max' => 80],
            [['params'], 'string'],
            [['css_class', 'view'], 'string', 'max' => 255],
            [['sort_order'], 'default', 'value' => 0],
            [['parent_id'], 'default', 'value' => 1],
            [['params'], 'default', 'value' => '{}'],
            ['type_id', 'exist', 'targetClass' => MenuHandler::className(), 'targetAttribute' => 'id'],
            ['view', 'default', 'value' => 'view'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'name' => Yii::t('app', 'Name'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'css_class' => Yii::t('app', 'Advanced Css Class'),
            'type_id' => Yii::t('malevich', 'Menu type'),
            'view' => Yii::t('app', 'View'),
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
            [
                'class' => Tree::className(),
                'sortOrder' => ['sort_order' => SORT_ASC],
                'activeAttribute' => false,
                'cascadeDeleting' => true,
            ],
            [
                'class' => HasProperties::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function search($params)
    {
        /* @var $query ActiveQuery */
        $query = self::find();
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
            ]
        );
        $query->andWhere(['parent_id' => $this->parent_id]);
        /* @var ActiveRecord|SearchModelTrait $this */
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        $this->addCondition($query, Menu::tableName(), 'name', true);
        return $dataProvider;
    }

    /* @return Menu[]|ActiveQuery */
    public function getChildren()
    {
        return $this->hasMany(Menu::className(), ['parent_id'=>'id'])->orderBy(['sort_order'=>SORT_ASC]);
    }

    /* @return Menu|ActiveQuery */
    public function getParent()
    {
        return $this->hasOne(Menu::className(), ['id' => 'parent_id'])->orderBy(['sort_order'=>SORT_ASC]);
    }

    /**
     * @return Video[]|ActiveQuery
     */
    public function getVideos()
    {
        return $this->hasMany(Video::className(), [
            'object_model_id' => 'id'
        ])->andWhere(
            ['object_id' => $this->object->id]);
    }

    /**
     * Returns handler model for this slider
     * @return MenuHandler|null
     */
    public function handler()
    {
        return MenuHandler::findByTypeId($this->type_id);
    }
}
