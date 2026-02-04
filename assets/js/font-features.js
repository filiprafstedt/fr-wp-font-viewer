jQuery(document).ready(function($){
    var $tableBody = $('#fr-font-features-table tbody');

    // Make rows sortable
    $tableBody.sortable();

    // Delete row
    $tableBody.on('click', '.fr-delete-feature', function(e){
        e.preventDefault();
        $(this).closest('tr').remove();
    });

    // Add new row
    $('#fr-add-feature').on('click', function(e){
        e.preventDefault();
        var index = $tableBody.find('tr').length;
        var newRow = '<tr data-index="'+index+'">' +
            '<td><input type="text" class="fr-feature-tag" value="" /></td>' +
            '<td><input type="text" class="fr-feature-name" value="" /></td>' +
            '<td><button class="button fr-edit-feature">Edit</button></td>' +
            '<td><button class="button fr-delete-feature">Delete</button></td>' +
            '</tr>';
        $tableBody.append(newRow);
    });
});