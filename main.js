$(document).ready(function(){
    $('#search_button').on('click',function(){
        let search_value = $('#input_search').val();

        if(search_value == ''){
            event.preventDefault();
            alert('Search field cant be empty.')
        }
    })
})