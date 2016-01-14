(function($, Craft)
{
	var OEmbed = new (Garnish.Base.extend({

		EmbedModal: null,

		init: function()
		{
			var that = this;
			var fn = Craft.AssetIndex.prototype;
			var superInit = fn.init;

			fn.init = function()
			{
				superInit.apply(this, arguments);

				that.initEmbedButton(this);
			};
		},

		initEmbedButton: function(assetIndex)
		{
			var $header = $('#extra-headers');
			var $buttons = assetIndex.$uploadButton.parent();
			var $buttonGroup = $('<div class="btngroup">').appendTo($header);

			assetIndex.$uploadButton.appendTo($buttonGroup);
			assetIndex.$uploadInput.appendTo($buttonGroup);
			$buttons.remove();

			var $embedButton = $('<div class="btn submit" data-icon="field">')
				.text(Craft.t('Embed link'))
				.appendTo($buttonGroup);

			this.addListener($embedButton, 'click', 'openEmbedModal');
		},

		openEmbedModal: function()
		{
			var modal = new this.EmbedModal();

			modal.show();
		}
	}))();

	window.OEmbed = OEmbed;

})(jQuery, Craft);
