$(document).ready(function() {

    var TYPE_CONTENT = 2;

    $type = $('#type');
    $language = $('#language')
    content = $('.nodeContent');
    nodeSettings = $('.nodeSettings');

    $type.on('change',function() {
        console.log("type: "+$(this).val());
        for(var i=0; i<nodeSettings.length; i++) {
            $node = $(nodeSettings[i]);
            $node.toggle($(this).val() == TYPE_CONTENT)

        }
    });

    $language.on('change',function() {
        var language = $(this).val();
        for(var i=0; i<content.length; i++) {
            $node = $(content[i]);
            if ($node.data('language') == language)
                $node.show();
            else
                $node.hide();
        }
    });

});