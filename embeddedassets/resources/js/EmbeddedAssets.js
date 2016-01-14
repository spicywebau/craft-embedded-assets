(function($, Craft)
{
	/**
	 * EmbeddedAssets Class (Singleton)
	 */
	var EmbeddedAssets = new (Garnish.Base.extend({

		assetIndex: null,

		init: function()
		{
			var that = this;
			var fn = Craft.AssetIndex.prototype;
			var superInit = fn.init;

			fn.init = function()
			{
				that.assetIndex = this;
				superInit.apply(this, arguments);

				that.initEmbedButton(this);
			};
		},

		parseUrl: function(url)
		{
			var params = {
				url: url
			};

			Craft.postActionRequest('embeddedAssets/parseUrl', params, $.proxy(function(response, textStatus)
			{
				if(textStatus == 'success')
				{
					this.trigger('parseUrl', response);
				}
				else
				{
					Craft.cp.displayError(Craft.t('An unknown error occurred.'));
				}
			}, this));
		},

		saveAsset: function(params)
		{
			console.log(params);
			Craft.postActionRequest('embeddedAssets/saveAsset', params, $.proxy(function(response, textStatus)
			{
				console.log(response, textStatus);
				if(textStatus == 'success')
				{
					this.trigger('saveAsset', response);

					console.log(response);
				}
				else
				{
					Craft.cp.displayError(Craft.t('An unknown error occurred.'));
				}
			}, this));
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
			var modal = new EmbeddedAssets.EmbedModal();

			modal.on('saveAsset', $.proxy(this.onSaveAsset, this));
			modal.show();
		},

		onSaveAsset: function(e)
		{
			this.saveAsset({
				folderId: this.assetIndex.getDefaultSourceKey().split(':')[1] | 0,
				media: e.media
			});
		}

	}))();

	window.EmbeddedAssets = EmbeddedAssets;

})(jQuery, Craft);
