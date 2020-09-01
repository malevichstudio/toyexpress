<?php
/**
 * @var app\components\WebView $this
 * @var boolean $displayHeader
 * @var app\modules\page\models\Page[] $models
 * @var string $widgetClass
 * @var object $categories
 * @var array $params
 * @var string $id
 * @var boolean $useScrollBar
 * @var boolean $useArrows
 */

use app\modules\image\widgets\ObjectImageWidget;
use kartik\icons\Icon;
use yii\helpers\Url;

?>

<div class="theme-widget <?= $widgetClass ?>">
    <?php if ($displayHeader): ?>
        <div class="widget-header">
            <div class="container">
                <?= $header ?>
            </div>
        </div>
    <?php endif ?>
    <div class="container">
        <div id="<?= $id ?>" class="slider-container">
            <?php foreach ($models as $index => $model) : ?>
            <?php if ($index < 3) : ?>
                <div class="item swiper-slide <?= !$index ? 'active' : ''; ?>">
                    <div class="inner-item">
                        <a href="<?= Url::toRoute(['@article', 'model' => $model])?>" class="image">
                            <?= ObjectImageWidget::widget(
                                [
                                    'limit' => 1,
                                    'model' => $model,
                                    'thumbnailOnDemand' => true,
                                    'thumbnailWidth' => 400,
                                    'thumbnailHeight' => 400,
                                ]
                            ) ?>
                        </a>
                        <div class="info">
                            <a href="<?= Url::toRoute(['@article', 'model' => $model])?>" class="title">
                                <?= $model->h1 ?>
                            </a>
                            <div class="announce">
                                <?= $model->announce ?>
                            </div>
                            <div class="date">
                                <?= Icon::show('clock-o') ?>
                                <?= Yii::$app->formatter->asDatetime($model->date_added, 'dd.MM.yyyy, HH:mm') ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif ?>
            <?php endforeach ?>
        </div>
        <div class="hidden lazy-slider" data-target="<?= $id ?>">
            <?php foreach ($models as $index => $model) : ?>
                <?php if ($index >= 3) : ?>
                    <div class="item swiper-slide <?= !$index ? 'active' : ''; ?>">
                        <div class="inner-item">
                            <a href="<?= Url::toRoute(['@article', 'model' => $model])?>" class="image">
                                <?= ObjectImageWidget::widget(
                                    [
                                        'limit' => 1,
                                        'model' => $model,
                                        'thumbnailOnDemand' => true,
                                        'thumbnailWidth' => 400,
                                        'thumbnailHeight' => 400,
                                    ]
                                ) ?>
                            </a>
                            <div class="info">
                                <a href="<?= Url::toRoute(['@article', 'model' => $model])?>" class="title">
                                    <?= $model->h1 ?>
                                </a>
                                <div class="announce">
                                    <?= $model->announce ?>
                                </div>
                                <div class="date">
                                    <?= Icon::show('clock-o') ?>
                                    <?= Yii::$app->formatter->asDatetime($model->date_added, 'dd.MM.yyyy, HH:mm') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif ?>
            <?php endforeach ?>
        </div>
    </div>
</div>
