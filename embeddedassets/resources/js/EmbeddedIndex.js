(function($)
{
	var EmbeddedIndex = Garnish.Base.extend({

		assetIndex: null,

		init: function(assetIndex)
		{
			this.assetIndex = assetIndex;

			this.initEmbedButton();
			this.initThumbnails();
		},

		initEmbedButton: function()
		{
			var assetIndex = this.assetIndex;
			var $buttonGroup = assetIndex.$uploadButton.parent()
				.removeClass('buttons')
				.addClass('btngroup');

			// Make sure the input is not the first (or last) element in the button group so border radii will be
			// applied correctly.
			assetIndex.$uploadInput.insertAfter(assetIndex.$uploadButton);

			var $embedButton = $('<div class="btn submit">')
				.text(Craft.t('Embed link'))
				.appendTo($buttonGroup);

			this.addListener($embedButton, 'click', 'openEmbedModal');
		},

		initThumbnails: function()
		{
			var that = this;

			EmbeddedAssets.on('saveAsset', function(e)
			{
				that.assetIndex.updateElements();
			});

			this.assetIndex.on('updateElements', function(e)
			{
				var view = e.target.view;
				var $elements = view.getAllElements();

				EmbeddedAssets.applyThumbnails($elements);
			});
		},

		openEmbedModal: function()
		{
			var modal = new EmbeddedAssets.EmbedModal();

			modal.on('saveAsset', $.proxy(this.onSaveAsset, this));
			modal.show();
		},

		onSaveAsset: function(e)
		{
			EmbeddedAssets.saveAsset({
				folderId: this.assetIndex.getDefaultSourceKey().split(':')[1] | 0,
				url: e.url,
				media: e.media
			});
		}

	});

	EmbeddedAssets.patchClass(Craft.AssetIndex, EmbeddedIndex);

	EmbeddedAssets.EmbeddedIndex = EmbeddedIndex;

})(jQuery);
