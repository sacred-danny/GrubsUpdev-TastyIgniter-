+function ($) {

    $('.btn-print-order').on("click", function(e) {
        e.preventDefault();
        var url = $(this).attr('href')
        jQuery('#print-display-area').load(url, function () {

            var printContent = document.getElementById('print-display-area');
            var WinPrint = window.open('', '', 'width=900,height=650');
            WinPrint.document.write(printContent.innerHTML);
            WinPrint.document.close();

            setTimeout(function () {

                WinPrint.focus();
                WinPrint.print();
                WinPrint.close();

            }, 200);

        });
        
    })
   
  
}(window.jQuery);