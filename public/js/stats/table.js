/**
 * Created with JetBrains PhpStorm.
 * User: Administrator
 * Date: 30.05.15
 * Time: 0:44
 * To change this template use File | Settings | File Templates.
 */

var statTableHelper = {
    init: function() {
        this.decorateTable();
        this.bindRejectStatusTooltip();
        this.bindAdPreviewTooltip();
    },
    decorateTable: function() {
        $('.icon-ban-circle').closest('tr').addClass('banned');
        $('.icon-trash').closest('tr').addClass('archived');
    },
    bindRejectStatusTooltip: function() {
        $('.icon-ban-circle').each(
            function() {
                var result = '';
                var element = this;

                $.post( '/vk/getRejectReason/' + $(this).data('banAdId'), function( data ) {
                    if (data.data) {
                        if (data.data.comment) {
                            result = '<b>Комментарий модератора:</b><br>' + data.data.comment + '<br><br>';
                        }
                        if (data.data.rule) {
                            data.data.rule.forEach(
                                function(res) {
                                    result += '<b>' + res.title + '</b><br>';
                                    if (res.paragraphs) {
                                        res.paragraphs.forEach(
                                            function(par){
                                                result += par + '<br>';
                                            }
                                        )
                                    }
                                }
                            )
                        }
                    }

                    $(element).bind('mouseenter',
                        function(){
                            $( element ).tooltip({
                                content: function() {
                                    return result;
                                }
                            })
                        }()
                    )
                });

            }
        );

    },
    bindAdPreviewTooltip: function() {
        var tooltipsStack = $('.ad-preview');
        tooltipsStack.each(
            function() {
                var result = '';
                var element = this;
                $(element).closest('td').bind('mouseenter',
                    function(){
                        setTimeout(function(){ $(element).tooltip() }, 0);

                        $.post( '/vk/getAdPreview/' + $(element).data('adPreview'), function( data ) {
                            if (data && data.html) {
                                result = data.html;
                            }
                            setTimeout(function(){ $(element).tooltip({content: result});}, 500);

                        });

                    }
                );
                $(element).closest('tr').bind('mouseleave',
                    function() {
                        tooltipsStack.each(
                            function(){
                                if ($(this).tooltip('instance')) {
                                    $(this).tooltip('close')
                                }
                            }
                        )
                    }
                );
            }
        );

    },
    checkAdsSettingsButtonStatus: function(el) {
        if (!!$('#dataTable').find('input[type=checkbox]:checked').length) {
            $('#button-ad-settings').prop('disabled', false);
        } else {
            $('#button-ad-settings').prop('disabled', true);
        }
    },
    declOfNum: function (number, titles) {
        var cases = [2, 0, 1, 1, 1, 2];
        return titles[ (number%100>4 && number%100<20)? 2 : cases[(number%10<5)?number%10:5] ];
    },
    sendData: function (element, data) {
        var adIds = [];
        $('#dataTable').find('input[type=checkbox]:checked').each(
            function() {
                var parentTr = $(this).parents("tr");
                parentTr.css('opacity', '0.5');
                parentTr.find('#ad-status').removeClass().addClass('icon-question-sign');
                adIds.push($(this).data('adId'));
            }
        );
        $.post( '/vk/adsControl/', {'action': $(element).data('action'), 'adIds': adIds, form_data: data}, function( data ) {
            if (data) {
                if (!!data.status) {
                    $('#dataTable').find('input[type=checkbox]:checked').each(
                        function() {
                            $(this).prop('checked', false);
                        }
                    );
                    w2popup.close();
                    $('#button-ad-settings').prop('disabled', true);
                } else {
                    setTimeout(function () {
                        $('#w2ui-popup .change-ads-settings-content #error-message span').text(data.error);
                        var height= $('#w2ui-popup .change-ads-settings-content').height();
                        $('#w2ui-popup .w2ui-msg-body').animate({"scrollTop":height}, 100);
                    }, 100);
                }
            }
        });
    },
    changeAdsSettings: function() {
        $('#change-ads-settings').w2popup(
            {width: 550,
                height: 465,
                title: 'Настройки',
                buttons   : '<button class="btn btn-green" data-action="settings" onclick="statTableHelper.sendData($(this), $(\'#w2ui-popup #change-ads-settings-form\').serializeArray());">Сохранить</button> '+
                    '<button class="btn" onclick="w2popup.close();">Отмена</button>',
                onOpen: function() {
                    setTimeout(function () {
                        var countAds = $('#dataTable').find('input[type=checkbox]:checked').length;
                        $('#w2ui-popup .change-ads-settings-content #adscounter')
                            .text('Для изменения выбрано ' + countAds + statTableHelper.declOfNum(countAds, [' объявление', ' объявления', ' объявлений']));
                    }, 100);

                }
            }
        );
    }
};