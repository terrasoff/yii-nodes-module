<div id="pages" class="card article_item">
    <div class="inner-topbg"></div>
    <div class="content">
        <div class="inner-views obj-page flowclear">
            <div class="f-left">
                <div class="block">

                    <?php if (Yii::app()->user->isModerator()) { ?>
                        <a href="<?php echo Yii::app()->createUrl(NodesModule::ACTION_EDIT,array('id'=>$model->id));?>"><?php echo NodesModule::t('edit:node');?></a>
                    <?php }?>

                    <h3><?php echo $model->getName();?>
                        <span class="date"><?php echo $model->getDate();?></span>
                    </h3>


                    <div class="text">
                        <img class="f-left" title="" src="/images/realty/0.png" alt=""/>
                        <?php echo $model->getText();?>
                    </div>

                    <div class="clear"></div>

                    </div><!--article-box-->
                    <div class="meta"><?php echo $model->getSeo();?></div>
                    <div class="clear"></div>

                </div>
            </div>
            <div class="f-right">
                <div class="add-shade"></div>

                <?php $this->renderPartial('/nodes_index',array('data'=>$index));?>

                <div class="clear"></div>
            </div>
        </div><!--inner-view-->
    </div>
</div>