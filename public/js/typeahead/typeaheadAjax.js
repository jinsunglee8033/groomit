function suggestion (searchType) {

    // for that, have to find download and save in the file system.
    var inputNode =  '#' + searchType + ' .typeahead';

    /* remote : call from DB */
     var suggestions = new Bloodhound({
         datumTokenizer: Bloodhound.tokenizers.obj.whitespace('id', 'name'),
         queryTokenizer: Bloodhound.tokenizers.whitespace,
         remote: {
             url : '/admin/get_users/' + searchType + '/%QUERY',
             wildcard: '%QUERY'
         }
     });

    // var suggestions = new Bloodhound({
    //     datumTokenizer: Bloodhound.tokenizers.obj.whitespace('id', 'name'),
    //     queryTokenizer: Bloodhound.tokenizers.whitespace,
    //     prefetch: {
    //         url: 'data/' + searchType + '.json',
    //         ttl: 1 // in milliseconds
    //     }
    // });

    $(inputNode).typeahead({
        hint: true,
        highlight: true,
        minLength: 2
    }, {
        name: 'search',
        displayKey: 'name',
        valueKey: 'id',
        limit: 10,
        source: suggestions,
        templates: {
            empty: [
                '<div class="alert alert-warning">No matching result found.</div>'
            ]
        }
    });

////// below is not working
    // add input hidden field to pass each id values
    $(inputNode).on("typeahead:selected typeahead:autocompleted", function(e,datum) {
        if (searchType == 'user') {
            $('#user_id').val(datum.id);
        } else if (searchType == 'groomer') {
            $('#groomer_id').val(datum.id);
        }


    });

}