<?php
$url = Yii::app()->createUrl(NodesModule::ACTION_EDIT,array('id'=>$model->idNode));
?>

<div>
    <?php if (Yii::app()->user->isModerator()) { ?>
        <a href="<?= $url ?>" class="label node-preview-icon label-primary"><span class="glyphicon glyphicon-wrench"></span></a>
        <div><a href="<?php echo $model->getUrl($parent);?>"><?php echo $model->getName();?></a></div>
    <?php }?>
</div>