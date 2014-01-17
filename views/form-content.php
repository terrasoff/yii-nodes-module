<?php
/**
 * @var NodeContent $content
 * @var Node $container
 */
?>

<div data-language="<?php echo $content->language;?>"<?php echo $content->isCurrent() ? "" : " style='display: none;'";?>>

    <?php if (isset($content->idNodeContent)) echo CHtml::hiddenField($content->getAttributeName('idNodeContent'),$content->idNodeContent);?>


    <div class="form-group">
        <label class="col-sm-2 control-label">Имя</label>
        <div class="col-sm-5">
            <?php echo CHtml::textField($content->getAttributeName('name'),$content->name, array('class'=>'form-control'));?>
        </div>
    </div>

    <div class="nodeSettings"<?php if ($container->isContainer()) {echo " style='display: none;'";};?>>
        <div class="form-group cut">
            <label class="col-sm-2 control-label">Кат</label>
            <div class="col-sm-5">
                <?php echo CHtml::textArea($content->getAttributeName('cut'),$content->cut,array('class'=>'form-control redactor'));?>
            </div>
        </div>

        <div class="form-group body">
            <label class="col-sm-2 control-label">Текст</label>
            <div class="col-sm-5">
                <?php echo CHtml::textArea($content->getAttributeName('body'),$content->body,array('class'=>'form-control redactor'));?>
            </div>
        </div>

    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">keywords</label>
        <div class="col-sm-5">
            <?php echo CHtml::textArea($content->getAttributeName('keywords'), $content->keywords, array('class'=>'form-control'));?>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">description</label>
        <div class="col-sm-5">
            <?php echo CHtml::textArea($content->getAttributeName('description'), $content->description, array('class'=>'form-control'));?>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">SEO-блок</label>
        <div class="col-sm-5">
            <?php echo CHtml::textArea($content->getAttributeName('seo'), $content->seo, array('class'=>'form-control'));?>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">Опубликовано</label>
        <div class="col-sm-2">
            <?php echo CHtml::checkBox($content->getAttributeName('isPublished'),(int)$content->isPublished); ?>
        </div>
    </div>

</div>