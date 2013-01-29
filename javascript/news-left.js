/**
 * on load, submit the empty searchform to get default results list instead of 'empty' screen
 */
jQuery(function($) {
    if ($('#SearchForm_holder .tabstrip').length > 0) {
        /**
         * tabstrip may not be initialized when this is executed
         */
        // the initial search
        var activeTab = ($('#SearchForm_holder .tabstrip li:first a:first').attr('href')).replace(/^.*#/, '');
        $('#Form_Search' + activeTab).submit();

        // the onchange search
        $('#SearchForm_holder .tabstrip li a').live('click', function() {
            var activeTab = ($(this).attr('href')).replace(/^.*#/, '');
            $('#Form_Search' + activeTab).submit();
        });
    } else if ($('#ModelClassSelector').length > 0) {
        // the initial search
        $('#Form_Search' + $('#ModelClassSelector select').val()).submit();

        // the onchange search
        $('#ModelClassSelector select').change(function(){
            $('#Form_Search' + $('#ModelClassSelector select').val()).submit();
        });
    }
});
