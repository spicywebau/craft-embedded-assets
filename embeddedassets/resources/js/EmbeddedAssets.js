(function($, Craft)
{
	/**
	 * EmbeddedAssets Class (Singleton)
	 */
	var EmbeddedAssets = new (Garnish.Base.extend({

		assetIndex: null,
		thumbnails: [], // Populated in EmbeddedAssetsPlugin.php

		init: function()
		{
			var that = this;

			var AssetIndexFn = Craft.AssetIndex.prototype;
			var AssetIndexInit = AssetIndexFn.init;

			AssetIndexFn.init = function()
			{
				AssetIndexInit.apply(this, arguments);

				new that.EmbeddedIndex(this);
			};

			/*
			var AssetSelectInputFn = Craft.AssetSelectInput.prototype;
			var AssetSelectInputResetElements = AssetSelectInputFn.resetElements;

			AssetSelectInputFn.resetElements = function()
			{
				new that.EmbeddedInput(this);
				AssetSelectInputResetElements.apply(this, arguments);

				var $elements = this.$elements;
			};
			*/
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
			Craft.postActionRequest('embeddedAssets/saveEmbeddedAsset', params, $.proxy(function(response, textStatus)
			{
				if(textStatus == 'success')
				{
					var media = response.media;
					this.setThumbnail(media.id, media.thumbnailUrl);

					this.trigger('saveAsset', response);
				}
				else
				{
					Craft.cp.displayError(Craft.t('An unknown error occurred.'));
				}
			}, this));
		},

		getThumbnail: function(assetId)
		{
			return this.thumbnails && this.thumbnails[assetId] ? this.thumbnails[assetId] : null;
		},

		setThumbnail: function(assetId, thumbnail)
		{
			if(!this.thumbnails)
			{
				this.thumbnails = {};
			}

			this.thumbnails[assetId | 0] = thumbnail;
		}

	}))();

	window.EmbeddedAssets = EmbeddedAssets;

})(jQuery, Craft);
