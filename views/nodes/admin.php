<div class="row">
    <div class="col-lg-12">
    <h1>Модуль управления страницами</h1>

    <?php if (Yii::app()->user->isModerator()) { ?>
            <a href="<?php echo Yii::app()->createUrl(NodesModule::ACTION_EDIT); ?>">
                <button class="btn btn-primary">
                    <span class="glyphicon glyphicon-plus-sign"></span>
                    <?=NodesModule::t('add page');?>
                </button>
            </a>

    <?php } ?>
    </div>
    <hr/>

    <div class="col col-lg-4 col-md-4">
        <h2>Рубрикатор</h2>
        <?= $this->renderPartial('/nodes_index',array('data'=>$index));?>
    </div>
    <div class="col col-lg-8 col-md-8">
        <h2>Страницы</h2>
        <?php if ($parent) {?>
            <a href="<?php echo Yii::app()->createUrl(NodesModule::ACTION_EDIT,array('id'=>$parent->idNode)); ?>">
                <?php echo NodesModule::t('edit');?>
            </a>
        <?php } ?>

        <div class='items'>
            <?php if (count($items)) foreach($items as $item) {
                $this->renderPartial('/node_preview',array(
                    'model'=>$item,
                    'parent'=>$parent,
                ));
            }?>
        </div>
    </div>
</div>