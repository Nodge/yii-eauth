/*
 * Yii EAuth extension.
 * @author Maxim Zemskov
 * @link http://github.com/Nodge/yii-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
jQuery(function($) {
    var popup;

	$.fn.eauth = function(options) {
		options = $.extend({
			id: '',
			popup: {
				width: 450,
				height: 380
			}
		}, options);

		return this.each(function() {
		    var el = $(this);
		    el.click(function() {
	            if (popup !== undefined)
	                popup.close();

	            var redirect_uri, url = redirect_uri = this.href;
				if (url.indexOf('redirect_uri=')==-1)
					url += (url.indexOf('?') >= 0 ? '&' : '?') + 'redirect_uri=' + encodeURIComponent(redirect_uri);
				url += '&js';

	            var centerWidth = ($(window).width() - options.popup.width) / 2;
	            var centerHeight = ($(window).height() - options.popup.height) / 2;

				popup = window.open(url, "yii_eauth_popup", "width=" + options.popup.width + ",height=" + options.popup.height + ",left=" + centerWidth + ",top=" + centerHeight + ",resizable=yes,scrollbars=no,toolbar=no,menubar=no,location=no,directories=no,status=yes");
	            popup.focus();

	            return false;
	        });
		});
	};
});
