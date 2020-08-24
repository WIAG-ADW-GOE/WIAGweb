$(document).ready(function() {
    $('.js-name-autocomplete').autocomplete({hint: false}, [
        {
            source: function(query, cb) {
                cb([
                    {value: 'foo'},
                    {value: 'bar'}
                ])
            }
        }
    ]);
});

