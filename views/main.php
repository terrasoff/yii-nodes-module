<div class="card">
    <div class="inner-topbg"></div>
    <div class="content">
        <div class="backbox flowclear">
            <a class="pie" title="" href="<?echo $backlink;?>">
                <?echo NodesModule::t('back','realty');?></a>
            <a class="linksearch" title="" href="/"><?php echo RealtyModule::t('search again','realty');?></a>
        </div>

        <div class="inner-views obj-page flowclear">
            <div class="f-left"></div>
            <div class="f-right">
                <div class="add-ad pie">
                    <span class="button"><a title="подать объявление" href="<?php echo RealtyModule::ACTION_ADD;?>">подать объявление</a></span>
                </div>
                <div class="add-shade"></div>

                <div class="sideMenu">
                    <h2><?php echo NodesModule::t('information');?>:</h2>
                    <ul class="uslugi"></ul>
                </div>
                <div class="clear"></div>
            </div>
        </div><!--inner-view-->
    </div>
</div>