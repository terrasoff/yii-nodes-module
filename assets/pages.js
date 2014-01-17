$(document).ready(function(){
    $Pages = new Pages;
    // нажали на кнопку "еще" - подгружаем страницы
    $('#more').on('click',$Pages.getMorePages);
});

Pages = function() {
    self = this;
    self.pages = $('#pages');
    self.items = $('#pages').find('.article-box .items');
    self.total = $('#pages').find('.total_items').html().replace(/[^\d]+/g,'')-0;

    // подгружаем страницы
    this.getMorePages = function() {
        var data = {
            category_id: category_id,
            total:  self.items.find('.article-item').length
        }
        if (data.total >= self.total) {
            self.onCompleteLoading();
            return;
        }
        console.dir(data);
        $.ajax({
            data: data, type: "POST",
            success: function(re) {
                var r= $.parseJSON(re);
                console.dir(r);
                // если есть че добавить
                if (r.items != undefined) {
                    // добавляем
                    self.items.append(r.items);
                    // все объекты загружены
                    if (self.items.find('.article-item').length >= self.total)
                        // прячем кнопку "еще"
                        self.pages.find('#more span').hide();
                }
            },
            error:onError
        });
    }

} // Pages