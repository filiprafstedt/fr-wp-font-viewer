jQuery(document).ready(function ($) {

    var $tableBody = $('#fr-font-features-table tbody');

    // Drag & drop
    $tableBody.sortable({
        axis: 'y'
    });

    // Delete row
    $tableBody.on('click', '.fr-delete-feature', function (e) {
        e.preventDefault();
        $(this).closest('tr').remove();
    });

    // Add new row
    $('#fr-add-feature').on('click', function (e) {
        e.preventDefault();

        var row = `
            <tr>
                <td><input type="text" class="fr-feature-tag" value="" /></td>
                <td><input type="text" class="fr-feature-name" value="" /></td>
                <td><button class="button fr-edit-feature">Edit</button></td>
                <td><button class="button fr-delete-feature">Delete</button></td>
            </tr>
        `;

        $tableBody.append(row);
    });

    // Save features
    $('#fr-save-features').on('click', function (e) {
        e.preventDefault();

        var features = [];

        $tableBody.find('tr').each(function () {
            var tag = $(this).find('.fr-feature-tag').val().trim();
            var name = $(this).find('.fr-feature-name').val().trim();

            if (tag && name) {
                features.push({
                    tag: tag,
                    name: name
                });
            }
        });

        $.post(FRFontFeatures.ajax_url, {
            action: 'fr_save_font_features',
            nonce: FRFontFeatures.nonce,
            features: features
        })
        .done(function () {
            alert('Font Features saved');
        })
        .fail(function () {
            alert('Error saving Font Features');
        });
    });

});
