/**
 * Created by User1 on 09.07.15.
 */

var vkAdsUploader = {
    countriesSelectBox: '#uploader-form #country',
    citiesSelectBox: '#uploader-form #city',
    userAgeFrom: '#uploader-form #user-age-from',
    userAgeTo: '#uploader-form #user-age-to',
    init: function() {
        this.initVkCountries();
        this.initUserAge();
    },
    initVkCountries: function() {
        $(vkAdsUploader.countriesSelectBox).change(
            function() {
               vkAdsUploader.updateVkCities(this);
            }
        );
        $.post( '/uploader/getVkCountries', function( data ) {
            if (data && data.countries) {
                $.each(data.countries, function( index, value ) {
                    $(vkAdsUploader.countriesSelectBox).append('<option value=' + value.id + '>' + value.name + '</option>');
                });
            }
        });
    },
    updateVkCities: function(selectbox) {
        $(vkAdsUploader.citiesSelectBox).find('option').remove();
        $(vkAdsUploader.citiesSelectBox).attr('disabled', !$(selectbox).val());

        $.post( '/uploader/getVkCities', {params: {country_id: $(selectbox).val()}}, function( data ) {
            if (data && data.cities) {
                $.each(data.cities, function( index, value ) {
                    $(vkAdsUploader.citiesSelectBox).append('<option value=' + value.id + '>' + value.name + '</option>');
                });
            }
        });
    },
    initUserAge: function() {
        var list = [];
        for (var i = 12; i <= 80; i++) {
            list.push(i);
        }
        $.each(list, function( index, value ) {
            $(vkAdsUploader.userAgeFrom).append('<option value=' + value + '>' + value + '</option>');
            $(vkAdsUploader.userAgeTo).append('<option value=' + value + '>' + value + '</option>');
        });
    }
};