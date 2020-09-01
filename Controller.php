<?php

namespace app\modules\shop\controllers;

use app\backend\actions\PropertyHandler;
use app\backend\actions\UpdateEditable;
use app\backend\components\BackendController;
use app\models\Object;
use app\modules\image\widgets\views\AddImageAction;
use app\modules\image\widgets\RemoveAction;
use app\modules\image\widgets\SaveInfoAction;
use app\modules\image\widgets\UploadAction;
use app\modules\shop\models\Manufacturer;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\properties\HasProperties;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

/**
 * BackendManufacturerController implements the CRUD actions for Tag model.
 */
class BackendManufacturerController extends BackendController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['shop manage'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function actions()
    {
        return [
            'addImage' => [
                'class' => AddImageAction::className(),
            ],
            'upload' => [
                'class' => UploadAction::className(),
                'upload' => 'theme/resources/manufacturer-images',
            ],
            'remove' => [
                'class' => RemoveAction::className(),
                'uploadDir' => 'theme/resources/manufacturer-images',
            ],
            'save-info' => [
                'class' => SaveInfoAction::className(),
            ],
            'property-handler' => [
                'class' => PropertyHandler::className(),
                'modelName' => Manufacturer::className()
            ],
            'update-editable' => [
                'class' => UpdateEditable::className(),
                'modelName' => Manufacturer::className(),
                'allowedAttributes' => [
                    'active' => function (Manufacturer $model) {
                        if ($model === null || $model->active === null) {
                            return null;
                        }
                        if ($model->active === 1) {
                            $label_class = 'label-success';
                            $value = 'Active';
                        } else {
                            $value = 'Inactive';
                            $label_class = 'label-default';
                        }
                        return Html::tag(
                            'span',
                            Yii::t('app', $value),
                            ['class' => "label $label_class"]
                        );
                    },
                ],
            ],
        ];
    }

    /**
     * Lists all Manufacturer models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new Manufacturer();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    /**
     * Updates or create an Manufacturer model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionUpdate($id = null)
    {
        if (null === $object = Object::getForClass(Manufacturer::className())) {
            throw new ServerErrorHttpException;
        }
        /** @var null|Manufacturer|HasProperties|ActiveRecordHelper $model */
        $model = null;
        if (null === $id) {
            $model = new Manufacturer();
        } else {
            $model = $this->findModel($id);
        }
        $post = Yii::$app->request->post();

        if ($model->load($post) && $model->validate()) {

            if (isset($post['videos'])) {
                $videos = Json::decode($post['videos']);
            } else {
                $videos = [];
            }
            $model->saveVideos($videos);

            $save_result = $model->save();
            $model->saveProperties($post);
            if ($save_result) {
                $this->runAction('save-info', ['model_id' => $model->id]);
                $model->invalidateTags();
                $action = Yii::$app->request->post('action', 'save');
                if (Yii::$app->request->post(HasProperties::FIELD_ADD_PROPERTY_GROUP)
                    || Yii::$app->request->post(HasProperties::FIELD_REMOVE_PROPERTY_GROUP)) {
                    $action = 'save';
                }
                $returnUrl = Yii::$app->request->get('returnUrl', ['index']);
                switch ($action) {
                    case 'next':
                        return $this->redirect(
                            [
                                'update',
                                'returnUrl' => $returnUrl,
                            ]
                        );
                    case 'back':
                        return $this->redirect($returnUrl);
                    default:
                        return $this->redirect(
                            Url::toRoute([
                                'update',
                                'id' => $model->id,
                                'returnUrl' => $returnUrl,
                            ])
                        );
                }
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot save data'));
            }
        }
        return $this->render(
            'update',
            [
                'model' => $model,
                'object' => $object,
            ]
        );
    }

    /**
     * Deletes an existing Manufacturer model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return Response
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();
        return $this->redirect(['index']);
    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function actionRemoveAll()
    {
        $items = Yii::$app->request->post('items', []);
        if (!empty($items)) {
            $items = Manufacturer::find()->where(['in', 'id', $items])->all();
            foreach ($items as $item) {
                $item->delete();
            }
        }
        return $this->redirect(['index']);
    }

    /**
    * Finds the Manufacturer model based on its primary key value.
    * If the model is not found, a 404 HTTP exception will be thrown.
    * @param integer $id
    * @return Manufacturer the loaded model
    * @throws NotFoundHttpException if the model cannot be found
    */
    protected function findModel($id)
    {
        if (($model = Manufacturer::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
