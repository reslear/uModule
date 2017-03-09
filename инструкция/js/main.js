$(function () {
    $('body').scrollspy({target: '.col-questions'});
    
    var currentAnchorId;
    
    $('.col-questions').on('activate.bs.scrollspy', function () {
        currentAnchorId = $(this).find('.active a').attr('href');  
            $('.api-tab').attr('href',currentAnchorId);
    });
    
    
    function setEqualHeight(blocks) {
    var tallestblock = 0;
    blocks.each(
        function () {
            currentHeight = $(this).height();
            if (currentHeight > tallestblock) {
                tallestblock = currentHeight;
            }
        }
    );
    blocks.height(tallestblock);
    }

    $('.api-code-w').each(function(){
        setEqualHeight($(this).find('code'));
    });
    
    $('.api-tab').on('hide.bs.tab', function(e) {
        $('body').removeClass($(e.target).data("lang"));
        $('body').addClass($(e.relatedTarget).data("lang"));
    });
    
    $('.questions-l').affix({
      offset: {top: 145}
    });
    
    $('.api-tabs').affix({
      offset: {top: 145}
    });

    $('.questions-l a').each(function(){
        $(this).click(function () {
            var el = $($(this).attr('href'));
            var offsetTop = el.offset().top;
            
            $('body,html').animate({
                scrollTop: offsetTop
            }, 300);
            return false;
        });
    });
    
    var $toTop = $('#totop');
    
    $(window).scroll(function () {
      if ($(this).scrollTop() >= 500) {
          $toTop.addClass('visible');
      } else {
        $toTop.removeClass('visible');
      }
    });   
});

$(function(){
	$('.code').click(function() {
	    var e = this;
	    if (window.getSelection) {
	        var s = window.getSelection();
	        if (s.setBaseAndExtent) {
	            s.setBaseAndExtent(e, 0, e, e.innerText.length - 1);
	        } else {
	            var r = document.createRange();
	            r.selectNodeContents(e);
	            s.removeAllRanges();
	            s.addRange(r);
	        }
	    } else if (document.getSelection) {
	        var s = document.getSelection();
	        var r = document.createRange();
	        r.selectNodeContents(e);
	        s.removeAllRanges();
	        s.addRange(r);
	    } else if (document.selection) {
	        var r = document.body.createTextRange();
	        r.moveToElementText(e);
	        r.select();
	    }
	});

});