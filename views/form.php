<?php
/** @var Node $model */
?>


<div class="row">

    <?php if ($errors) { ?>
        <div class="errors"><?php CVarDumper::dump($errors,10,true);?></div>
    <?php } ?>

    <?php
    $this->widget('ext.TMenu', array(
        'htmlOptions'=>array(
            'class'=>'breadcrumb',
        ),
        'items'=>array(
            array(
                'label'=>'Модуль управления страницами',
                'url'=>Yii::app()->createUrl(NodesModule::ACTION_ADMIN),
            ),
            array(
                'label'=>'Редактор',
            )
        )
    ));
    ?>

    <form name="Node" method="POST" id="EditorForm" class="form-horizontal" role="form">
        <div class="form-group">
            <?php if (count($nodes)>1) {?>
                <label class="col-sm-2 control-label input">Родитель</label>
                <div class="col-sm-2">
                    <?php echo CHtml::activeDropDownList($model,'parent_id',$nodes, array('class'=>'form-control'));?>
                </div>
            <?php } ?>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">Тип</label>
            <div class="col-sm-2">
                <?php echo CHtml::activeDropDownList($model,'type',array_flip(NodesModule::$types),array('id'=>'type', 'class'=>'form-control'));?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label">Язык</label>
            <div class="col-sm-2">
                <?php echo CHtml::dropDownList('language',Yii::app()->language,array_combine(Yii::app()->params['languages'],Yii::app()->params['languages']), array('id'=>'language', 'class'=>'form-control'));?>
            </div>
        </div>

        <?php
            foreach ($model->content as $i=>$content) {
                $this->renderPartial('/form-content',array(
                    'content'=>$content,
                    'container'=>$model,
                ));
            }

            $widget = $this->widget('nodes.widgets.NodesRedactorWidget', array(
                // You can either use it for model attribute
                'selector' => '.redactor',
                // Some options, see http://imperavi.com/redactor/docs/
                'options' => array(
                    'imageUpload'=> '/nodes/nodes/upload',
                ),
            ))
        ?>

        <div class="form-group">
            <div class="col-sm-4 col-lg-offset-2">
                <button type="submit" id='btn_save' class="btn btn-primary">Сохранить</button>
                <?php if (!$model->isNewRecord) {?>
                    <button type="submit" id='btn_delete' class="btn btn-danger" formaction="<?php echo Yii::app()->createUrl(NodesModule::ACTION_DELETE,array('id'=>$model->idNode));?>" >Удалить</button>
                <?php }?>
            </div>
        </div>

    </form>
    <br class='clear' />
</div>